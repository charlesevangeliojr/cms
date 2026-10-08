(() => {
    if (window.cmsDashboardTrafficInstalled) return;
    window.cmsDashboardTrafficInstalled = true;
    let timer, retryTimer, controller, panel, busy = false, generation = 0, failures = 0;
    const number = new Intl.NumberFormat();
    function describeMinute(element, data) {
        const offset = Number(element.dataset.minuteOffset) || 0;
        if (!data.available || !data.realtimeUpdatedAt) return 'Analytics data unavailable';
        const reportedAt = new Date(data.realtimeUpdatedAt);
        if (!Number.isFinite(reportedAt.getTime())) return 'Analytics timestamp unavailable';
        const bucketDate = new Date(Math.floor(reportedAt.getTime() / 60000) * 60000 - offset * 60000);
        const time = new Intl.DateTimeFormat(undefined, {
            timeZone: data.timezone || undefined,
            year: 'numeric', month: 'short', day: 'numeric',
            hour: 'numeric', minute: '2-digit',
        }).format(bucketDate);
        return `${time} · ${number.format(Number(element.dataset.users) || 0)} active users`;
    }
    function showMinuteTooltip(element, data) {
        const chart = panel?.querySelector('[data-traffic-chart]');
        const tooltip = chart?.querySelector('[data-traffic-tooltip]');
        if (!chart || !tooltip) return;
        const rect = element.getBoundingClientRect();
        const chartRect = chart.getBoundingClientRect();
        const left = Math.max(80, Math.min(chartRect.width - 80, rect.left - chartRect.left + rect.width / 2));
        tooltip.textContent = describeMinute(element, data);
        tooltip.style.left = left + 'px';
        tooltip.hidden = false;
    }
    function initializeMinuteTooltips() {
        const initialData = {
            available: panel?.dataset.trafficAvailable === 'true',
            timezone: panel?.dataset.trafficTimezone,
            realtimeUpdatedAt: panel?.dataset.trafficUpdatedAt,
        };
        panel?.querySelectorAll('[data-traffic-minute]').forEach(element => {
            element._trafficData = initialData;
            element.title = describeMinute(element, initialData);
            element.setAttribute('aria-label', element.title);
            if (element.dataset.tooltipReady) return;
            element.dataset.tooltipReady = 'true';
            element.addEventListener('pointerenter', () => showMinuteTooltip(element, element._trafficData || {}));
            element.addEventListener('pointerleave', () => {
                if (!element.matches(':focus')) panel?.querySelector('[data-traffic-tooltip]')?.setAttribute('hidden', '');
            });
            element.addEventListener('focus', () => showMinuteTooltip(element, element._trafficData || {}));
            element.addEventListener('blur', () => panel?.querySelector('[data-traffic-tooltip]')?.setAttribute('hidden', ''));
        });
    }
    function setOptionalText(target, selector, text) {
        const element = target.querySelector(selector);
        if (element) element.textContent = text;
    }
    function render(data) {
        panel.querySelectorAll('[data-traffic-value]').forEach(element => {
            const available = element.dataset.trafficValue === 'monthlyUsers' ? data.monthlyAvailable : data.available;
            element.textContent = available && Number.isFinite(data[element.dataset.trafficValue]) ? number.format(data[element.dataset.trafficValue]) : '—';
        });
        const minutes = Array.from({length: 30}, (_, index) => Math.max(0, Number(data.minutes?.[index]) || 0));
        const peak = Math.max(1, ...minutes);
        panel.querySelector('[data-traffic-peak]').textContent = number.format(peak);
        panel.querySelector('[data-traffic-mid]').textContent = number.format(peak / 2);
        panel.querySelectorAll('[data-traffic-minute]').forEach((element, index) => {
            element.dataset.users = minutes[index];
            element._trafficData = data;
            element.title = describeMinute(element, data);
            element.setAttribute('aria-label', element.title);
            element.querySelector('[data-traffic-bar]').style.height = (data.available ? minutes[index] / peak * 100 : 0) + '%';
        });
        const focusedMinute = panel.querySelector('[data-traffic-minute]:focus');
        if (focusedMinute) showMinuteTooltip(focusedMinute, data);
        panel.querySelector('[data-traffic-chart]').setAttribute('aria-label', data.available ? 'Active users per minute, oldest to newest, for the last 30 minutes. ' + minutes.join(', ') : 'Active users per minute: data unavailable.');
        panel.querySelector('[data-traffic-month]').textContent = data.monthLabel + ' · month to date';
        setOptionalText(panel, '[data-traffic-timezone]', data.timezone);
        panel.querySelector('[data-traffic-indicator]').className = 'h-2 w-2 rounded-full ' + (data.available ? 'bg-emerald-500' : 'bg-gray-400');
        panel.querySelector('[data-traffic-state]').textContent = data.available ? 'Live · updates every minute' : 'Awaiting data';
        setOptionalText(panel, '[data-traffic-notice]', !data.configured ? 'Connect Google Analytics to display website traffic. Unavailable statistics are shown as —.' : !data.available || !data.monthlyAvailable ? 'Some statistics are currently unavailable. The dashboard will retry automatically.' : 'Monthly totals are unique users, not the sum of daily or minute counts. Monthly reports may be delayed.');
    }
    async function refresh() {
        if (document.hidden || busy || !panel?.isConnected) return;
        busy = true;
        controller = new AbortController();
        const requestController = controller;
        const requestGeneration = generation;
        const currentPanel = panel;
        const deadline = setTimeout(() => requestController.abort(), 45000);
        let retryDelay;
        try {
            const endpoint = new URL(currentPanel.dataset.endpoint, window.location.href);
            if (endpoint.origin !== window.location.origin) throw new Error('Traffic endpoint must use the current origin');
            const response = await fetch(endpoint.href, {headers: {Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest'}, credentials: 'same-origin', cache: 'no-store', signal: requestController.signal});
            if (generation !== requestGeneration || !currentPanel.isConnected) return;
            if ([401, 403, 419].includes(response.status) || response.redirected) {
                currentPanel.querySelector('[data-traffic-state]').textContent = 'Refresh stopped';
                setOptionalText(currentPanel, '[data-traffic-notice]', 'Reload the dashboard to continue refreshing statistics.');
                currentPanel.querySelector('[data-traffic-indicator]').className = 'h-2 w-2 rounded-full bg-gray-400';
                stop(); return;
            }
            if (response.status === 429) {
                const retryAfter = response.headers.get('Retry-After');
                const seconds = Number(retryAfter);
                retryDelay = Number.isFinite(seconds) && seconds > 0 ? seconds * 1000 : 60000;
            }
            if (!response.ok) throw new Error('Traffic request failed: HTTP ' + response.status);
            const data = await response.json();
            if (typeof data.available !== 'boolean' || typeof data.monthlyAvailable !== 'boolean' || !Array.isArray(data.minutes)) throw new Error('Invalid analytics response');
            if (generation === requestGeneration && panel === currentPanel && panel.isConnected) {
                render(data);
                failures = 0;
                clearTimeout(retryTimer);
            }
        } catch (error) {
            if (generation === requestGeneration && panel === currentPanel && currentPanel.isConnected) {
                failures++;
                currentPanel.querySelector('[data-traffic-state]').textContent = 'Update delayed · retrying';
                currentPanel.querySelector('[data-traffic-indicator]').className = 'h-2 w-2 rounded-full bg-amber-500';
                setOptionalText(currentPanel, '[data-traffic-notice]', 'The last update could not be loaded. Showing the previous statistics while retrying automatically.');
                clearTimeout(retryTimer);
                retryTimer = setTimeout(refresh, retryDelay ?? Math.min(10000 * 2 ** (failures - 1), 60000));
            }
        } finally {
            clearTimeout(deadline);
            if (generation === requestGeneration) busy = false;
        }
    }
    function stop() {
        generation++;
        clearInterval(timer);
        clearTimeout(retryTimer);
        controller?.abort();
        panel = null;
        busy = false;
        failures = 0;
    }
    function initialize() {
        const nextPanel = document.querySelector('[data-dashboard-traffic]');
        if (nextPanel === panel && panel?.isConnected) return;
        stop();
        panel = nextPanel;
        if (panel) {
            initializeMinuteTooltips();
            timer = setInterval(refresh, 60000);
        }
    }
    document.addEventListener('turbo:load', initialize);
    document.addEventListener('turbo:before-render', stop);
    document.addEventListener('turbo:before-cache', stop);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
    initialize();
})();
