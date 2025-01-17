<?php

namespace Tests\Feature\DataTables;

use App\Article;
use App\Conversion;
use App\Model\Property\SelectedProperty;
use App\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Remp\LaravelSso\Http\Middleware\VerifyJwtToken;
use Tests\TestCase;

class ConversionsDataTableTest extends TestCase
{
    use RefreshDatabase;

    /** @var Article[] */
    protected $articles;

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            VerifyJwtToken::class,
        ]);

        Property::factory()->create(['uuid' => 'prop_1']);
        Property::factory()->create(['uuid' => 'prop_2']);

        $this->articles = [
            'prop1' => Article::factory()->create(['property_uuid' => 'prop_1']),
            'prop2' => Article::factory()->create(['property_uuid' => 'prop_2']),
        ];

        // assign conversions
        $this->articles['prop1']->conversions()->saveMany(
            Conversion::factory()->count(2)->make(['article_id' => $this->articles['prop1']])
        );
        $this->articles['prop2']->conversions()->saveMany(
            Conversion::factory()->count(3)->make(['article_id' => $this->articles['prop2']])
        );
    }

    public function testAllConversions()
    {
        $json = $this->request();
        $json->assertSuccessful();
        $json->assertJsonPath('recordsTotal', 5);
    }

    public function testPropertyConversions()
    {
        /** @var SelectedProperty $selectedProperty */
        $selectedProperty = resolve(SelectedProperty::class);
        $selectedProperty->setToken('prop_1');

        $json = $this->request();
        $json->assertSuccessful();
        $json->assertJsonPath('recordsTotal', 2);

        /** @var SelectedProperty $selectedProperty */
        $selectedProperty = resolve(SelectedProperty::class);
        $selectedProperty->setToken('prop_2');

        $json = $this->request();
        $json->assertSuccessful();
        $json->assertJsonPath('recordsTotal', 3);
    }

    private function request()
    {
        return $this->getJson(route('conversions.json'));
    }
}