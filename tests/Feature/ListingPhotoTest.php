<?php

namespace Tests\Feature;

use App\Filament\Resources\Listings\Pages\EditListing;
use App\Filament\Resources\Listings\Pages\ListListings;
use App\Models\Business;
use App\Models\User;
use App\Services\Listings\ListingPhotos;
use Database\Seeders\NextKeepersPlatformSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Admins upload a photo per listing; buyers see it on cards and the listing page. */
class ListingPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(ListingPhotos::DISK);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        return $admin;
    }

    private function photo(int $width = 3000, int $height = 2000, string $name = 'shop.jpg'): UploadedFile
    {
        return UploadedFile::fake()->image($name, $width, $height);
    }

    public function test_store_makes_a_resized_photo_and_a_cropped_thumbnail(): void
    {
        $path = ListingPhotos::store($this->photo());

        $this->assertTrue(ListingPhotos::isOwnPath($path));
        $disk = Storage::disk(ListingPhotos::DISK);
        $disk->assertExists([$path, ListingPhotos::thumbPath($path)]);

        [$w, $h] = getimagesizefromstring($disk->get($path));
        $this->assertSame([1600, 1067], [$w, $h], 'long side capped at 1600');
        [$tw, $th] = getimagesizefromstring($disk->get(ListingPhotos::thumbPath($path)));
        $this->assertSame([640, 480], [$tw, $th], 'thumbnail cropped to 4:3');

        // Small photos are not blown up.
        [$sw] = getimagesizefromstring($disk->get(ListingPhotos::store($this->photo(800, 600))));
        $this->assertSame(800, $sw);
    }

    public function test_store_rejects_files_that_are_not_photos(): void
    {
        $this->expectExceptionMessage('Upload a JPG, PNG or WebP image.');
        ListingPhotos::store(UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'));
    }

    public function test_store_rejects_svg(): void
    {
        $this->expectExceptionMessage('Upload a JPG, PNG or WebP image.');
        ListingPhotos::store(UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'));
    }

    public function test_replacing_or_removing_a_photo_deletes_the_old_files(): void
    {
        $listing = Business::factory()->create();
        $first = ListingPhotos::store($this->photo());
        ListingPhotos::attach($listing, $first);

        $second = ListingPhotos::store($this->photo());
        ListingPhotos::attach($listing, $second);

        $disk = Storage::disk(ListingPhotos::DISK);
        $disk->assertMissing([$first, ListingPhotos::thumbPath($first)]);
        $disk->assertExists([$second, ListingPhotos::thumbPath($second)]);

        ListingPhotos::attach($listing, null);
        $disk->assertMissing([$second, ListingPhotos::thumbPath($second)]);
        $this->assertNull($listing->fresh()->photo_path);
    }

    public function test_photo_route_serves_only_listing_photos(): void
    {
        $path = ListingPhotos::store($this->photo());

        $this->get(ListingPhotos::url($path))->assertOk()->assertHeader('Cache-Control', 'immutable, max-age=31536000, public');
        $this->get(ListingPhotos::url($path, thumb: true))->assertOk();
        $this->get('/listing-photos/'.str_repeat('a', 40).'.webp')->assertNotFound();
        $this->get('/listing-photos/..%2F..%2F.env')->assertNotFound();
    }

    public function test_admin_uploads_a_photo_on_the_edit_page(): void
    {
        $this->actingAs($this->admin());
        $listing = Business::factory()->create();

        Livewire::test(EditListing::class, ['record' => $listing->getRouteKey()])
            ->fillForm(['photo_path' => $this->photo()])
            ->call('save')
            ->assertHasNoFormErrors();

        $path = $listing->fresh()->photo_path;
        $this->assertTrue(ListingPhotos::isOwnPath($path));
        Storage::disk(ListingPhotos::DISK)->assertExists(ListingPhotos::thumbPath($path));
    }

    public function test_edit_page_rejects_non_images(): void
    {
        $this->actingAs($this->admin());
        $listing = Business::factory()->create();

        Livewire::test(EditListing::class, ['record' => $listing->getRouteKey()])
            ->fillForm(['photo_path' => UploadedFile::fake()->create('deck.pdf', 50, 'application/pdf')])
            ->call('save')
            ->assertHasFormErrors(['photo_path']);

        $this->assertNull($listing->fresh()->photo_path);
    }

    public function test_quick_photo_action_on_the_listings_table(): void
    {
        $this->actingAs($this->admin());
        $listing = Business::factory()->create();

        Livewire::test(ListListings::class)
            ->callAction(TestAction::make('photo')->table($listing), ['photo_path' => $this->photo(1200, 900)])
            ->assertHasNoFormErrors()
            ->assertNotified('Photo saved');

        $path = $listing->fresh()->photo_path;
        $this->assertTrue(ListingPhotos::isOwnPath($path));
        Storage::disk(ListingPhotos::DISK)->assertExists([$path, ListingPhotos::thumbPath($path)]);

        Livewire::test(ListListings::class)
            ->callAction(TestAction::make('photo')->table($listing), ['photo_path' => null])
            ->assertNotified('Photo removed');

        $this->assertNull($listing->fresh()->photo_path);
        Storage::disk(ListingPhotos::DISK)->assertMissing($path);
    }

    public function test_buyers_see_the_photo_or_a_placeholder(): void
    {
        $this->seed(NextKeepersPlatformSeeder::class);
        $withPhoto = Business::factory()->published()->create(['name' => 'Erie Dental']);
        ListingPhotos::attach($withPhoto, ListingPhotos::store($this->photo()));
        Business::factory()->published()->create(['name' => 'Monroe Home Care']);

        $user = User::factory()->create();

        $this->actingAs($user)->get('/discover')->assertOk()
            ->assertSee($withPhoto->fresh()->photoUrl(thumb: true), false)
            ->assertSee('No photo');

        $this->actingAs($user)->get(route('business.show', ['id' => $withPhoto->id]))->assertOk()
            ->assertSee($withPhoto->fresh()->photoUrl(), false)
            ->assertSee('Photo of Erie Dental');
    }
}
