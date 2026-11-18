<?php
namespace Tests\Feature;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class NewsTest extends TestCase {
    use RefreshDatabase;
    protected function setUp(): void { parent::setUp();$this->seed(); }
    public function test_home_displays_seeded_news(): void { $this->get('/')->assertOk()->assertSee('The business edition')->assertSee('Lulu Retail plans Philippine expansion, franchise opportunities and bond offering'); }
    public function test_legacy_favicon_url_redirects_to_svg(): void { $this->get('/favicon.ico')->assertRedirect('/favicon.svg'); }
    public function test_moms_touch_article_is_seeded_with_its_image_and_proposed_program_details(): void {
        $slug='moms-touch-eyes-philippine-expansion-as-korean-fast-food-brand-accelerates-global-growth';
        $this->get('/')->assertOk()->assertSee('Mom’s Touch Eyes Philippine Expansion as Korean Fast-Food Brand Accelerates Global Growth');
        $this->get('/articles/'.$slug)
            ->assertOk()
            ->assertSee('/images/moms-touch.jpg')
            ->assertSee('Proposed Joint Venture Program')
            ->assertSee('would require formal verification before any public offering or enrollment');
    }
    public function test_search_filters_results(): void { $this->get('/?q=Lulu+Retail')->assertOk()->assertSee('Lulu Retail plans Philippine expansion, franchise opportunities and bond offering')->assertDontSee('Little faces, big smiles'); }
    public function test_categories_filter_results(): void { $this->get('/?category=Banking')->assertOk()->assertSee('Banking')->assertDontSee('BIR issues electronic invoicing guidelines'); }
    public function test_pagination_and_missing_article(): void { $this->get('/?page=2')->assertOk()->assertSee('Page 2');$this->get('/articles/does-not-exist')->assertNotFound(); }
    public function test_article_uses_original_source(): void { $article=Article::where('source_url','!=','')->first();$this->get('/articles/'.$article->slug)->assertOk()->assertSee($article->title)->assertSee($article->source_url); }
    public function test_lulu_announcement_has_one_correct_source_and_removes_bir_duplicate(): void {
        $luluUrl='https://philstar-business.com/articles/lulu-retail-plans-philippine-expansion-franchise-opportunities-and-bond-offering';
        $birUrl='https://philstar-business.com/articles/bir-issues-electronic-invoicing-guidelines';
        Article::create(['slug'=>'bir-issues-electronic-invoicing-guidelines','title'=>'Lulu Retail plans Philippine expansion, franchise opportunities and bond offering','excerpt'=>'Duplicate announcement','author'=>'User-supplied content','category'=>'Companies','image'=>'','source_url'=>$birUrl,'published_at'=>'2026-09-26 00:00:00','body'=>['Duplicate announcement']]);
        $this->seed();
        $this->assertSame(1,Article::where('title','Lulu Retail plans Philippine expansion, franchise opportunities and bond offering')->count());
        $this->assertSame($luluUrl,Article::where('slug','lulu-retail-plans-philippine-expansion-franchise-opportunities-and-bond-offering')->value('source_url'));
        $this->assertDatabaseMissing('articles',['source_url'=>$birUrl]);
    }
    public function test_stale_bir_link_is_hidden_and_lulu_article_is_featured_first(): void {
        $birUrl='https://philstar-business.com/articles/bir-issues-electronic-invoicing-guidelines';
        Article::create(['slug'=>'bir-issues-electronic-invoicing-guidelines','title'=>'Lulu Retail plans Philippine expansion, franchise opportunities and bond offering','excerpt'=>'Duplicate announcement','author'=>'User-supplied content','category'=>'Companies','image'=>'','source_url'=>$birUrl,'published_at'=>'2026-11-19 00:00:00','body'=>['Duplicate announcement']]);
        $this->get('/')
            ->assertOk()
            ->assertDontSee($birUrl)
            ->assertSeeInOrder([
                '/articles/lulu-retail-plans-philippine-expansion-franchise-opportunities-and-bond-offering',
                '/articles/moms-touch-eyes-philippine-expansion-as-korean-fast-food-brand-accelerates-global-growth',
            ], false);
        $this->get('/articles/bir-issues-electronic-invoicing-guidelines')->assertNotFound();
    }
    public function test_empty_search_and_input_validation(): void { $this->get('/?q=zzzznomatch')->assertOk()->assertSee('No stories found');$this->getJson('/?page=-1')->assertUnprocessable(); }
    public function test_seeding_is_idempotent(): void { $count=Article::count();$this->seed();$this->assertSame($count,Article::count()); }
}
