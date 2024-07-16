@extends('laravel-query-execute::layouts.app')

@push('css')
    <link rel="stylesheet" href="{{ asset('laravel-query-execute/css/codemirror.min.css') }}">
    <link rel="stylesheet" href="{{ asset('laravel-query-execute/css/mirror-show-hint.min.css') }}">
    <link rel="stylesheet" href="{{ asset('laravel-query-execute/css/datatable.min.css') }}">
    <style>
        .CodeMirror {
            height: 40vh;
            border: 1px solid #ddd;
            font-size: 12px;
            width: 100%;
        }
        .dataTables_wrapper {
            width: 100%;
            overflow-x: auto;
        }
        .tab-pane {
            width: 100%;
            overflow-x: auto;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-3">
            <div class="form-group border">
                <select class="form-control" aria-label="Default select example" id="select-database">
                    <option disabled>Select Database</option>
                    @foreach($connections as $connection => $alias)
                        <option @if ($loop->first) selected @endif value="{{$connection}}">{{$alias}}</option>
                    @endforeach
                </select>
            </div>

            <div class="input-group mb-3 border">
                <input type="text" class="form-control" id="table-name" placeholder="Table name"
                       aria-describedby="button-addon2">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" id="button-addon2"><i class="fa fa-refresh"
                                                                                                  aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div>
                <div class="list-group" style="height: 100vh; overflow: auto" id="list-table">
                </div>
            </div>
        </div>
        <div class="col-9 border-left">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="mb-2">
                    <h3 class="mb-0">New Query</h3>
                    <span>No description</span>
                </div>
                <div class="action">
                    <button id="format" class="btn btn-primary"><i class="fa fa-flask" aria-hidden="true"></i> Format
                        SQL
                    </button>
                    <button id="save" class="btn btn-success"><i class="fa fa-floppy-o" aria-hidden="true"></i> Save
                    </button>
                    <button id="execute" class="btn btn-info"><i class="fa fa-chevron-circle-right"
                                                                 aria-hidden="true"></i> Execute
                    </button>
                </div>
            </div>
            <div>
                <textarea id="highlight" name="highlight">
                                        SELECT *
FROM config_templates JOIN users ON config_templates.id = users.id WHERE config_templates.id > 0
                select * from users
                </textarea>
            </div>
            <div id="message" class="mt-3 alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <span id="alert"></span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="container mt-2" id="result-query">
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('laravel-query-execute/js/codemirror.min.js') }}"></script>
    <script src="{{ asset('laravel-query-execute/js/sql.min.js') }}"></script>
    <script src="{{ asset('laravel-query-execute/js/show-hint.min.js') }}"></script>
    <script src="{{ asset('laravel-query-execute/js/sql-hint.min.js') }}"></script>
    <script src="{{ asset('laravel-query-execute/js/sql-formatter.min.js') }}"></script>
    <script src="{{ asset('laravel-query-execute/js/datatable.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            const editor = CodeMirror.fromTextArea(document.getElementById("highlight"), {
                lineNumbers: true,
                mode: "text/x-mysql",
                extraKeys: {
                    "Shift-Space": "autocomplete", // For macOS
                    "Ctrl-Space": "autocomplete" // For Windows/Linux
                }
            });

            editor.on("inputRead", function (cm, change) {
                if (!cm.state.completionActive && /^[a-zA-Z]+$/.test(change.text[0])) {
                    cm.showHint({completeSingle: false});
                }
            });

            $("#format").click(function () {
                const rawSQL = editor.getValue();
                const formattedSQL = sqlFormatter.format(rawSQL, {language: 'sql'});
                editor.setValue(formattedSQL);
            });

            $('#message').hide()

            const insertTextAtCursor = (text) => {
                var doc = editor.getDoc();
                var cursor = doc.getCursor();
                doc.replaceRange(text, cursor);
            }
            $('#append-table').click(function () {
                console.log($(this).attr('table'))
            })

            const getTable = (option) => {
                $('#message').hide()
                fetch('{{route('query-execute.table')}}?connection=' + option, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        "X-CSRF-Token": "{{csrf_token()}}",
                    },
                    credentials: "same-origin"
                }).then(function (response) {
                    return response.json()
                }).then(function (data) {
                    if (data.success) {
                        let tableHtml = '';
                        for (const table in data.data) {
                            let columnHtml = '';
                            for (const column in data.data[table]) {
                                columnHtml +=
                                    `<p class="d-flex align-items-center mb-1">
                <i class="fa fa-circle ml-2" aria-hidden="true"></i>
                <span class="ml-2 column-content">${data.data[table][column]}</span>
                <i class="fa fa-angle-double-right ml-auto mr-2 column-arrow" style="cursor: pointer" role="button" aria-hidden="true"></i>
            </p>`;
                            }
                            tableHtml +=
                                `<div class="mb-2">
            <div class="list-group-item d-flex align-items-center p-1">
                <i class="fa fa-table ml-2" aria-hidden="true"></i>
                <span class="ml-2 table-content" type="button" data-toggle="collapse" data-target="#collapse-${table}" aria-expanded="false" aria-controls="collapse-${table}">${table}</span>
                <i class="fa fa-angle-double-right ml-auto mr-2 table-arrow" style="cursor: pointer" aria-hidden="true"></i>
            </div>
            <div class="collapse" id="collapse-${table}">
                <div class="border pt-1 pb-1 pl-2 ml-3 mt-1">${columnHtml}</div>
            </div>
        </div>`;
                        }
                        $('#message').hide()
                        $('#list-table').html(tableHtml)
                    } else {
                        $('#message').show()
                        $('#message #alert').text(data.message)
                    }
                })
            }

            $(document).on('click', '.table-arrow', function() {
                let tableContent = $(this).siblings('.table-content').text();
                insertTextAtCursor(tableContent)
            });

            $(document).on('click', '.column-arrow', function() {
                let columnContent = $(this).siblings('.column-content').text();
                insertTextAtCursor(columnContent)
            });

            $("#execute").click(function () {
                executeQuery();
            });

            $('#select-database').on('change', function () {
                getTable();
            });
            $('#button-addon2').click(function () {
                getTable($("#select-database option:selected").val())
            })
            getTable($("#select-database option:selected").val())

            const executeQuery = () => {
                $('#message').hide()
                let query = editor.getSelection();
                if (query === '' || query === undefined) {
                    query = editor.getValue();
                }

                $('#result-query').html(`<ul class="nav nav-tabs" id="tableTabs" role="tablist">
                </ul>
                <div class="tab-content mt-2" id="tabContent">
                </div>`);

                fetch('{{route('query-execute.execute')}}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        "X-CSRF-Token": "{{csrf_token()}}",
                    },
                    credentials: "same-origin",
                    body: JSON.stringify({
                        connection: $("#select-database option:selected").val(),
                        query: query
                    })
                }).then(function (response) {
                    return response.json()
                }).then(function (data) {
                    if (data.success) {
                        $('#message').hide()

                        function createTable(result, tableId) {
                            // Create table HTML
                            const tableHtml = `
                    <table id="${tableId}" class="display" style="width:100%">
                        <thead>
                            <tr id="${tableId}-header"></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                `;
                            $(`#${tableId}-content`).append(tableHtml);

                            // Append column headers
                            const columns = result.columns;
                            columns.forEach(column => {
                                $(`#${tableId}-header`).append(`<th>${column}</th>`);
                            });

                            // Initialize DataTable
                            $(`#${tableId}`).DataTable({
                                responsive: true,
                                scrollX: true,
                                data: result.data,
                                columns: columns.map(column => ({data: column}))
                            });
                        }

                        // Loop through each result and create a tab and corresponding table
                        data.data.forEach((item, index) => {
                            const tableId = `table-${index}`;
                            const isActive = index === 0 ? 'active' : '';

                            // Append tab header
                            $('#tableTabs').append(`
                    <li class="nav-item">
                        <a class="nav-link ${isActive}" id="${tableId}-tab" data-toggle="tab" href="#${tableId}-content" role="tab" aria-controls="${tableId}-content" aria-selected="true">${item.table} (${item.time})</a>
                    </li>
                `);

                            // Append tab content
                            $('#tabContent').append(`
                    <div class="tab-pane fade show ${isActive}" id="${tableId}-content" role="tabpanel" aria-labelledby="${tableId}-tab">
                    </div>
                `);

                            // Create and initialize the table inside the tab
                            createTable(item.result, tableId);
                        });
                    } else {
                        $('#message').show()
                        $('#message #alert').text(data.message)
                    }
                })
            }
        });
    </script>
@endpush
