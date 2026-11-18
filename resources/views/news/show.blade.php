@extends('layouts.app')

@section('title', $article->title)

@section('content')
    <article class="detail">
        <a class="back" href="{{ route('news.index') }}">
            ← All business stories
        </a>

        <span class="tag">{{ $article->category }}</span>

        <h1>{{ $article->title }}</h1>

        <div class="byline">
            By {{ $article->author }}
            · {{ $article->published_at->format('F j, Y') }}
        </div>

        @if($article->image)
            <div class="media">
                <img
                    class="photo"
                    src="{{ $article->image }}"
                    alt=""
                >
            </div>
        @endif

        @if($article->body)
            <div class="article-body">
                @foreach($article->body as $paragraph)
                    @if(\Illuminate\Support\Str::startsWith($paragraph, '### '))
                        <h2>{{ \Illuminate\Support\Str::after($paragraph, '### ') }}</h2>
                    @else
                        <p>{!! preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', e($paragraph)) !!}</p>
                    @endif
                @endforeach
            </div>
        @else
            <p class="excerpt">{{ $article->excerpt }}</p>
        @endif

        @if($article->source_url && \Illuminate\Support\Str::startsWith($article->source_url, ['https://', 'http://']))
            <div class="note">
                This is the excerpt included in the supplied page.
                The full article is available from its original publisher.
            </div>

            <a
                class="button"
                href="{{ $article->source_url }}"
                target="_blank"
                rel="noopener noreferrer"
            >
                Read original article ↗
            </a>
        @endif
    </article>
@endsection
