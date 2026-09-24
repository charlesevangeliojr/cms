<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('banners');
    }

    public function test_admin_sees_only_persisted_banners(): void
    {
        $admin = $this->superAdmin();
        Banner::create($this->bannerData('Persisted Banner'));
        Banner::create($this->bannerData('Inactive Banner', false));

        $response = $this->actingAs($admin)->get(route('banners.index'));

        $response->assertOk()
            ->assertSee('Persisted Banner')
            ->assertSee('Inactive Banner')
            ->assertSee('2')
            ->assertDontSee('Autumn Promotional Hero Banner');
    }

    public function test_admin_can_create_a_banner_with_an_uploaded_image(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->post(route('banners.store'), [
            'title' => 'Database Banner',
            'position' => Banner::POSITIONS[0],
            'target_url' => '/database-banner',
            'is_active' => '1',
            'image' => $this->fakeBannerImage(),
        ]);

        $response->assertRedirect(route('banners.index'))
            ->assertSessionHas('success', 'Banner created successfully.');

        $banner = Banner::sole();

        $this->assertDatabaseHas('banners', [
            'id' => $banner->id,
            'title' => 'Database Banner',
            'position' => Banner::POSITIONS[0],
            'target_url' => '/database-banner',
            'is_active' => true,
        ]);
        Storage::disk('banners')->assertExists($banner->image_path);
    }

    public function test_banner_images_larger_than_two_megabytes_are_rejected(): void
    {
        $admin = $this->superAdmin();
        $image = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true);

        $response = $this->actingAs($admin)->post(route('banners.store'), [
            'title' => 'Oversized Banner',
            'position' => Banner::POSITIONS[0],
            'target_url' => '/oversized-banner',
            'is_active' => '1',
            'image' => UploadedFile::fake()->createWithContent(
                'oversized.png',
                $image.str_repeat('0', (3 * 1024 * 1024) - strlen($image)),
            ),
        ]);

        $response->assertSessionHasErrors('image');
        $this->assertDatabaseCount('banners', 0);
    }

    public function test_admin_can_update_and_replace_a_banner_image(): void
    {
        $admin = $this->superAdmin();
        $banner = Banner::create($this->bannerData('Original Banner'));
        $oldImagePath = $banner->image_path;
        Storage::disk('banners')->put($oldImagePath, 'original image');

        $response = $this->actingAs($admin)->put(route('banners.update', $banner), [
            'title' => 'Updated Banner',
            'position' => Banner::POSITIONS[1],
            'target_url' => 'https://example.com/updated',
            'is_active' => '1',
            'image' => $this->fakeBannerImage('replacement.png'),
        ]);

        $response->assertRedirect(route('banners.index'))
            ->assertSessionHas('success', 'Banner updated successfully.');

        $banner->refresh();

        $this->assertSame('Updated Banner', $banner->title);
        $this->assertSame(Banner::POSITIONS[1], $banner->position);
        $this->assertNotSame($oldImagePath, $banner->image_path);
        Storage::disk('banners')->assertMissing($oldImagePath);
        Storage::disk('banners')->assertExists($banner->image_path);
    }

    public function test_admin_can_delete_a_banner_and_its_image(): void
    {
        $admin = $this->superAdmin();
        $banner = Banner::create($this->bannerData('Disposable Banner'));
        $imagePath = $banner->image_path;
        Storage::disk('banners')->put($imagePath, 'image');

        $response = $this->actingAs($admin)->delete(route('banners.destroy', $banner));

        $response->assertRedirect(route('banners.index'))
            ->assertSessionHas('success', 'Banner deleted successfully.');

        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
        Storage::disk('banners')->assertMissing($imagePath);
    }

    private function fakeBannerImage(string $name = 'banner.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true),
        );
    }

    private function superAdmin(): User
    {
        return User::factory()->create([
            'role' => 'Super Admin',
            'is_active' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function bannerData(string $title, bool $isActive = true): array
    {
        return [
            'title' => $title,
            'image_path' => str($title)->slug().'.jpg',
            'position' => Banner::POSITIONS[0],
            'target_url' => '/'.str($title)->slug(),
            'is_active' => $isActive,
        ];
    }
}
