<?php
namespace App\Http\Controllers;
use App\Models\Article;
use Illuminate\Http\Request;
class NewsController {
    private const REMOVED_ARTICLE_SLUG = 'bir-issues-electronic-invoicing-guidelines';
    private const REMOVED_ARTICLE_SOURCE = 'https://philstar-business.com/articles/bir-issues-electronic-invoicing-guidelines';
    private const FEATURED_ARTICLE_SLUG = 'lulu-retail-plans-philippine-expansion-franchise-opportunities-and-bond-offering';

    public function index(Request $request) {
        $validated=$request->validate(['q'=>'nullable|string|max:200','category'=>'nullable|string|max:50','page'=>'nullable|integer|min:1']);
        $q=trim($validated['q']??'');$category=$validated['category']??'';
        $query=Article::query()
            ->where('slug','!=',self::REMOVED_ARTICLE_SLUG)
            ->where('source_url','!=',self::REMOVED_ARTICLE_SOURCE);
        if($q!==''){$query->where(function($query)use($q){$query->where('title','like','%'.$q.'%')->orWhere('excerpt','like','%'.$q.'%')->orWhere('author','like','%'.$q.'%');});}
        if($category!==''){$query->where('category',$category);}
        return view('news.index',['articles'=>$query->orderByRaw('CASE WHEN slug = ? THEN 0 ELSE 1 END',[self::FEATURED_ARTICLE_SLUG])->orderByDesc('published_at')->orderBy('id')->paginate(12)->withQueryString(),'categories'=>Article::select('category')->distinct()->orderBy('category')->pluck('category'),'q'=>$q,'category'=>$category]);
    }
    public function show(Article $article) {
        abort_if(
            $article->slug===self::REMOVED_ARTICLE_SLUG
                || $article->source_url===self::REMOVED_ARTICLE_SOURCE,
            404
        );
        return view('news.show',compact('article'));
    }
}
