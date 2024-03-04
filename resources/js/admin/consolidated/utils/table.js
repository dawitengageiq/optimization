let datatable_options = {
    "scrollX": true,
    // "scrollY":        "50px",
    // "scrollCollapse": true,
    // "paging":         false,
    "searching": false,
    "ordering": false,
    "info":     false,
}

export function dataTable ($selector, $options) {
    if($options) datatable_options = $options;

    $($selector).dataTable(datatable_options);
}
