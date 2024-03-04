<div class="row">
    <div class="col-xs-12">
        <div id="myCarousel" class="carousel slide" data-ride="carousel" data-interval="false">
        <!-- Indicators -->
        @if(count($series) > 1)
            <ol class="carousel-indicators">
            @for($li = 0; $li <  count($series); $li++)
                <li id="a" data-target="#myCarousel" data-slide-to="{!! $li !!}" class="{!! ($li == 0) ? 'active' : '' !!}"></li>
            @endfor
            </ol>
        @endif

        <!-- Wrapper for slides -->
        <div class="carousel-inner" role="listbox">
        @for($li = 0; $li <  count($series); $li++)
            <div class="item {!! ($li == 0) ? 'active' : '' !!}" style="min-height: 850px;">
                <div class="consolidated-charts" id="series_{!! $li !!}" data-index="{!! $li !!}"></div>
            </div>
        @endfor
        </div>
        </div>
    </div>
</div>
