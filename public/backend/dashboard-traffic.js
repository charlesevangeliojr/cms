(() => {
    if (window.cmsDashboardTrafficInstalled) return;
    window.cmsDashboardTrafficInstalled = true;
    let timer, retryTimer, controller, panel, busy = false, generation = 0, failures = 0;
    const number = new Intl.NumberFormat();
    function describeMinute(element, data) {
        if (element.dataset.bucketLabel) {
            return data.available ? `${element.dataset.bucketLabel} · ${number.format(Number(element.dataset.users) || 0)} active users` : 'Analytics data unavailable';
        }
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
    function initializeMinuteTooltips(chartData) {
        const initialData = chartData || {
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
    function renderChart(chart) {
        const target = panel.querySelector('[data-traffic-bars]');
        const points = chart.points;
        const peak = Math.max(1, ...points.map(point => Math.max(0, Number(point.users) || 0)));
        const sameRange = target.dataset.range === chart.range;
        if (!sameRange || target.children.length !== points.length) {
            target.replaceChildren();
            points.forEach(() => {
                const element = document.createElement('div');
                element.dataset.trafficMinute = '';
                element.tabIndex = 0;
                element.setAttribute('role', 'img');
                element.className = 'group relative flex h-full min-w-0 flex-1 items-end outline-none focus-visible:ring-2 focus-visible:ring-indigo-400';
                const bar = document.createElement('div');
                bar.dataset.trafficBar = '';
                bar.className = 'w-full origin-bottom cursor-crosshair rounded-t-sm bg-indigo-500 transition-[height,background-color,transform] duration-150 ease-out group-hover:scale-y-110 group-hover:bg-indigo-700 group-hover:shadow-md group-focus-within:bg-indigo-700';
                element.append(bar);
                target.append(element);
            });
        }
        target.dataset.range = chart.range;
        Array.from(target.children).forEach((element, index) => {
            element.dataset.users = points[index].users;
            element.dataset.bucketLabel = points[index].label;
            element.querySelector('[data-traffic-bar]').style.height = (chart.available ? points[index].users / peak * 100 : 0) + '%';
        });
        initializeMinuteTooltips(chart);
        panel.querySelector('[data-traffic-peak]').textContent = number.format(peak);
        panel.querySelector('[data-traffic-mid]').textContent = number.format(peak / 2);
        panel.querySelector('[data-traffic-chart-title]').textContent = chart.title;
        panel.querySelector('[data-traffic-chart]').setAttribute('aria-busy', 'false');
        panel.querySelector('[data-traffic-chart]').setAttribute('aria-label', chart.available ? `${chart.title}, oldest to newest. ${points.map(point => point.users).join(', ')}` : `${chart.title}: data unavailable.`);
        panel.querySelector('[data-traffic-tooltip]').hidden = true;
        const focused = target.querySelector(':focus');
        if (focused) showMinuteTooltip(focused, chart);
        const axis = panel.querySelector('[data-traffic-axis]');
        axis.replaceChildren();
        const tickCount = Math.min(5, points.length);
        for (let index = 0; index < tickCount; index++) {
            const pointIndex = tickCount === 1 ? 0 : Math.round(index * (points.length - 1) / (tickCount - 1));
            const tick = document.createElement('span');
            tick.textContent = points[pointIndex].axis;
            axis.append(tick);
        }
        panel.querySelector('[data-traffic-chart-status]').textContent = !chart.available
            ? 'Chart data is currently unavailable. Retrying automatically.'
            : chart.range === '30m' ? 'Realtime activity · updates every minute.'
                : 'Hourly and daily reports refresh every 15 minutes. Recent activity may take 24–48 hours to be fully processed.';
    }
    function render(data) {
        panel.querySelectorAll('[data-traffic-value]').forEach(element => {
            const available = element.dataset.trafficValue === 'monthlyUsers' ? data.monthlyAvailable : data.available;
            element.textContent = available && Number.isFinite(data[element.dataset.trafficValue]) ? number.format(data[element.dataset.trafficValue]) : '—';
        });
        renderChart(data.chart);
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
            const range = currentPanel.querySelector('[data-traffic-range]').value;
            endpoint.searchParams.set('range', range);
            if (endpoint.origin !== window.location.origin) throw new Error('Traffic endpoint must use the current origin');
            const response = await fetch(endpoint.href, {headers: {Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest'}, credentials: 'same-origin', cache: 'no-store', signal: requestController.signal});
            if (generation !== requestGeneration || !currentPanel.isConnected) return;
            if ([401, 403, 419].includes(response.status) || response.redirected) {
                currentPanel.querySelector('[data-traffic-state]').textContent = 'Refresh stopped';
                currentPanel.querySelector('[data-traffic-chart]').setAttribute('aria-busy', 'false');
                currentPanel.querySelector('[data-traffic-chart-status]').textContent = 'Reload the dashboard to continue refreshing the chart.';
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
            if (typeof data.available !== 'boolean' || typeof data.monthlyAvailable !== 'boolean' || !Array.isArray(data.chart?.points) || data.chart.range !== range || typeof data.chart.available !== 'boolean') throw new Error('Invalid analytics response');
            if (generation === requestGeneration && panel === currentPanel && panel.isConnected) {
                render(data);
                failures = 0;
                clearTimeout(retryTimer);
            }
        } catch (error) {
            if (generation === requestGeneration && panel === currentPanel && currentPanel.isConnected) {
                failures++;
                currentPanel.querySelector('[data-traffic-state]').textContent = 'Update delayed · retrying';
                currentPanel.querySelector('[data-traffic-chart]').setAttribute('aria-busy', 'false');
                currentPanel.querySelector('[data-traffic-chart-status]').textContent = 'Chart update delayed. Retrying automatically.';
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
            const select = panel.querySelector('[data-traffic-range]');
            if (!select.dataset.rangeReady) {
                select.dataset.rangeReady = 'true';
                select.addEventListener('change', () => {
                    if (!panel?.isConnected) return;
                    generation++;
                    controller?.abort();
                    busy = false;
                    failures = 0;
                    clearTimeout(retryTimer);
                    panel.querySelector('[data-traffic-tooltip]').hidden = true;
                    panel.querySelector('[data-traffic-bars]').replaceChildren();
                    panel.querySelector('[data-traffic-axis]').replaceChildren();
                    panel.querySelector('[data-traffic-peak]').textContent = '—';
                    panel.querySelector('[data-traffic-mid]').textContent = '—';
                    const title = select.value === '30m' ? 'Active users per minute' : select.value === 'today' ? 'Active users per hour' : 'Active users per day';
                    panel.querySelector('[data-traffic-chart-title]').textContent = title;
                    panel.querySelector('[data-traffic-chart]').setAttribute('aria-label', title + ': loading.');
                    panel.querySelector('[data-traffic-chart]').setAttribute('aria-busy', 'true');
                    panel.querySelector('[data-traffic-chart-status]').textContent = 'Loading ' + select.selectedOptions[0].textContent.toLowerCase() + '…';
                    refresh();
                });
            }
            timer = setInterval(refresh, 60000);
        }
    }
    document.addEventListener('turbo:load', initialize);
    document.addEventListener('turbo:before-render', stop);
    document.addEventListener('turbo:before-cache', stop);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
    initialize();
})();
