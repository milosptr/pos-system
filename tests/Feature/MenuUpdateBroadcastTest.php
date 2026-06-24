<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Services\Pusher;
use Tests\TestCase;

/**
 * The POS loads the menu once and only re-fetches when it receives a
 * `menu-update` broadcast. These tests lock in that every inventory/category
 * mutation emits that event, so 86-ing an item or changing a price reaches the
 * waitstaff tablets without a manual reload.
 */
class MenuUpdateBroadcastTest extends TestCase
{
    use RefreshDatabase;

    private array $broadcastEvents = [];

    public function test_creating_inventory_broadcasts_menu_update()
    {
        $this->captureBroadcasts();
        $category = $this->makeCategory();

        $response = $this->postJson('/api/backoffice/inventory', [
            'category_id' => $category->id,
            'name' => 'Coca cola 0.25',
            'active' => 1,
            'sold_by' => 0,
            'price' => 210,
            'order' => 1,
        ]);

        $response->assertSuccessful();
        $this->assertContains('menu-update', $this->broadcastEvents);
    }

    public function test_updating_inventory_broadcasts_menu_update()
    {
        $item = $this->makeInventory();
        $this->captureBroadcasts();

        // 86 the item — the most common mid-service menu change.
        $response = $this->putJson('/api/backoffice/inventory/' . $item->id, [
            'active' => 0,
        ]);

        $response->assertSuccessful();
        $this->assertContains('menu-update', $this->broadcastEvents);
    }

    public function test_deleting_inventory_broadcasts_menu_update()
    {
        $item = $this->makeInventory();
        $this->captureBroadcasts();

        $response = $this->deleteJson('/api/backoffice/inventory/' . $item->id);

        $response->assertSuccessful();
        $this->assertContains('menu-update', $this->broadcastEvents);
    }

    public function test_updating_category_broadcasts_menu_update()
    {
        $category = $this->makeCategory();
        $this->captureBroadcasts();

        $response = $this->putJson('/api/backoffice/categories/' . $category->id, [
            'name' => 'Topli napici',
        ]);

        $response->assertSuccessful();
        $this->assertContains('menu-update', $this->broadcastEvents);
    }

    private function makeCategory(): Category
    {
        return Category::create([
            'name' => 'Pića',
            'parent_id' => 0,
            'order' => 1,
            'print' => 0,
        ]);
    }

    private function makeInventory(): Inventory
    {
        return Inventory::create([
            'category_id' => $this->makeCategory()->id,
            'name' => 'Coca cola 0.25',
            'active' => 1,
            'sold_by' => 0,
            'price' => 210,
            'qty' => 0,
            'order' => 1,
        ]);
    }

    /**
     * Replace the Pusher service with a mock that records every broadcast event
     * name into $this->broadcastEvents as it is triggered.
     */
    private function captureBroadcasts(): void
    {
        $this->broadcastEvents = [];
        $mock = $this->createMock(Pusher::class);
        $mock->method('trigger')
            ->willReturnCallback(function ($channel, $event, $data) {
                $this->broadcastEvents[] = $event;
            });
        $this->app->instance(Pusher::class, $mock);
    }
}
