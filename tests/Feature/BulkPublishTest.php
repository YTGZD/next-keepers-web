<?php

namespace Tests\Feature;

use App\Filament\Resources\Imports\Pages\ListListingImports;
use App\Filament\Resources\Imports\Pages\ViewListingImport;
use App\Filament\Resources\Listings\Pages\ListListings;
use App\Models\Business;
use App\Models\ListingImport;
use App\Models\User;
use App\Services\Listings\ListingPublisher;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Publishing many imported drafts at once instead of page by page. */
class BulkPublishTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $this->actingAs($admin);
    }

    private function import(array $attributes = []): ListingImport
    {
        return ListingImport::create($attributes + [
            'via' => 'command', 'file_name' => 'healthcare.xlsx', 'stored_path' => '/tmp/healthcare.xlsx', 'status' => 'done',
        ]);
    }

    public function test_publisher_publishes_only_real_drafts_and_skips_flagged_unless_asked(): void
    {
        $clean = Business::factory()->count(3)->create();
        $flagged = Business::factory()->create(['needs_review' => true]);
        $archived = Business::factory()->create(['status' => 'archived']);
        $sample = Business::factory()->sample()->create(['status' => 'draft']);
        $earlier = Business::factory()->create(['published_at' => now()->subMonth()]);

        $this->assertSame(['drafts' => 5, 'flagged' => 1], ListingPublisher::counts(Business::query()));

        $this->assertSame(4, ListingPublisher::publish(Business::query()));

        $clean->each(fn (Business $b) => $this->assertSame('active', $b->fresh()->status));
        $this->assertSame('draft', $flagged->fresh()->status);
        $this->assertSame('archived', $archived->fresh()->status);
        $this->assertSame('draft', $sample->fresh()->status);
        $this->assertTrue($earlier->fresh()->published_at->lt(now()->subWeek()), 'keeps the first publish date');
        $this->assertNotNull($clean->first()->fresh()->published_at);

        $this->assertSame(1, ListingPublisher::publish(Business::query(), includeFlagged: true));
        $this->assertSame('active', $flagged->fresh()->status);
        $this->assertFalse($flagged->fresh()->needs_review);
    }

    public function test_publish_all_shown_covers_every_page_of_the_filtered_review_queue(): void
    {
        Business::factory()->count(120)->create(['state' => 'NY']);
        Business::factory()->count(30)->create(['state' => 'TX']);
        Business::factory()->count(5)->create(['state' => 'NY', 'needs_review' => true]);

        Livewire::test(ListListings::class)
            ->filterTable('state', ['NY'])
            ->assertActionVisible('publishAllShown')
            ->assertActionHasLabel('publishAllShown', 'Publish all 125 drafts shown')
            ->mountAction('publishAllShown')
            ->assertMountedActionModalSee(['125 drafts match the current tab, filters and search', '5 of them are flagged'])
            ->callMountedAction()
            ->assertNotified('Published 120 listings');

        $this->assertSame(120, Business::where('state', 'NY')->where('status', 'active')->count());
        $this->assertSame(30, Business::where('state', 'TX')->where('status', 'draft')->count());
        $this->assertSame(5, Business::where('needs_review', true)->where('status', 'draft')->count());
    }

    public function test_publish_all_shown_is_hidden_when_nothing_is_left_to_publish(): void
    {
        Business::factory()->published()->count(3)->create();

        Livewire::test(ListListings::class, ['activeTab' => 'published'])->assertActionHidden('publishAllShown');
    }

    public function test_select_all_across_pages_then_publish_selected(): void
    {
        Business::factory()->count(80)->create();
        $flagged = Business::factory()->create(['needs_review' => true]);

        // "Select all 81" in the table header: Filament then tracks deselected rows instead of selected ones.
        Livewire::test(ListListings::class)
            ->set('isTrackingDeselectedTableRecords', true)
            ->set('deselectedTableRecords', [])
            ->callAction(TestAction::make('publish')->table()->bulk(), data: ['include_flagged' => true])
            ->assertNotified('Published 81 listings');

        $this->assertSame(81, Business::where('status', 'active')->count());
        $this->assertSame('active', $flagged->fresh()->status);
    }

    public function test_publish_selected_still_works_for_a_hand_picked_few(): void
    {
        [$a, $b, $c] = Business::factory()->count(3)->create()->all();

        Livewire::test(ListListings::class)
            ->selectTableRecords([$a->getKey(), $b->getKey()])
            ->callAction(TestAction::make('publish')->table()->bulk())
            ->assertNotified('Published 2 listings');

        $this->assertSame('active', $a->fresh()->status);
        $this->assertSame('draft', $c->fresh()->status);
    }

    public function test_an_import_can_publish_all_its_drafts_at_once(): void
    {
        $import = $this->import();
        $other = $this->import(['file_name' => 'western-ny.xlsx']);
        Business::factory()->count(60)->create(['listing_import_id' => $import->id]);
        Business::factory()->create(['listing_import_id' => $import->id, 'needs_review' => true]);
        Business::factory()->count(4)->create(['listing_import_id' => $other->id]);

        Livewire::test(ListListingImports::class)
            ->assertTableColumnStateSet('drafts_left', 61, $import)
            ->callAction(TestAction::make('publishDrafts')->table($import))
            ->assertNotified('Published 60 listings');

        $this->assertSame(60, $import->fresh()->published_count);
        $this->assertSame(4, Business::where('listing_import_id', $other->id)->where('status', 'draft')->count());

        Livewire::test(ViewListingImport::class, ['record' => $import->getRouteKey()])
            ->callAction('publishDrafts', data: ['include_flagged' => true])
            ->assertNotified('Published 1 listing');

        Livewire::test(ViewListingImport::class, ['record' => $import->getRouteKey()])->assertActionHidden('publishDrafts');
    }

    public function test_listings_can_be_filtered_by_import(): void
    {
        $import = $this->import();
        $mine = Business::factory()->count(2)->create(['listing_import_id' => $import->id]);
        $notMine = Business::factory()->create();

        Livewire::test(ListListings::class)
            ->filterTable('listing_import_id', $import->id)
            ->assertCanSeeTableRecords($mine)
            ->assertCanNotSeeTableRecords([$notMine]);
    }
}
