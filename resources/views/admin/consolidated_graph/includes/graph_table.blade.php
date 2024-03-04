<h4>
    Graph Details
    <a id="export_excel"
        onclick="CONSOLIDATEDCOMMON.spinner(this.id)"
        href="/admin/consolidatedGraph/{!! $export_link !!}"
        class="btn btn-primary pull-right">
        Export to Excel
    </a>
</h4>
<div  style="margin-bottom: 30px; overflow-x: scroll;">
    <table class="table table-bordered table-striped" role="grid">
        <thead>
            <tr>
            @if($inputs['chart_type'] == '#all_affiliate')
                <th>Rev Track ID</th>
            @else
                <th style="min-width: 75px;">Date</th>
            @endif
            @foreach($columns as $column)
            <th>
                {!! $legends[$column]['alias'] !!}
            </th>
            @endforeach
            </tr>
        </thead>
        <tbody>
        @foreach($records as $index => $record)
            <?php
            if($inputs['chart_type'] == '#all_affiliate') $category = 'CD' . $index;
            else $category = $index;

            if(!in_array($category, collect($categories)->flatten(1)->toArray())) continue;
            ?>

            <tr>
            <td>{!! $category !!}</td>
            @foreach ($columns as $column)
                @if(array_key_exists($column, $record))
                    <td>{!! $record[$column] !!}</td>
                @else
                    <td></td>
                @endif
            @endforeach
            </tr>
        @endforeach
        </tbody>
        </tfooter>
            <tr>
                <td></td>
                @foreach ($columns as $column)
                <th style="background-color: {!! $legends[$column]['color'] !!}"></th>
                @endforeach
            </tr>
        </tfooter>
    </table>
</div>
