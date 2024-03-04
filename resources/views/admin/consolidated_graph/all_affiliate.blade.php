@extends('admin.consolidated_graph.index')

@section('graph_header')
    <link href="{{ asset('css/admin/consolidated_chart.min.css') }}" rel="stylesheet" media="all">
@stop

@section('graph_search')
    @include('admin.consolidated_graph.includes.graph_search')
@stop

@section('graph_custom_settings')
    @include('admin.consolidated_graph.includes.graph_custom_settings')
@stop

@section('graph_slider')
    @include('admin.consolidated_graph.includes.graph_slider')
@stop

@section('graph_table')
    @include('admin.consolidated_graph.includes.graph_table')
@stop

@section('graph_footer')
    @include('admin.consolidated_graph.includes.graph_footer')
@stop
