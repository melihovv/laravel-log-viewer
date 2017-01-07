<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel log viewer</title>

    <!-- Bootstrap -->
    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet"
          href="https://cdn.datatables.net/plug-ins/9dcbecd42ad/integration/bootstrap/3/dataTables.bootstrap.css">


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style>
        body {
            padding: 25px;
        }

        h1 {
            font-size: 1.5em;
            margin-top: 0;
        }

        .stack {
            font-size: 0.85em;
        }

        .date {
            min-width: 75px;
        }

        .text {
            word-break: break-all;
        }

        a.llv-active {
            z-index: 2;
            background-color: #f5f5f5;
            border-color: #777;
        }
    </style>
</head>

<body>
<div class="container-fluid">
    <div class="row">

        <div class="col-sm-3 col-md-2 sidebar">
            <h1>Laravel Log Viewer</h1>
            <p class="text-muted">
                <i>by <a href="https://github.com/melihovv">@melihovv</a></i>
            </p>

            @if (count($parentDirs))
                <ul class="breadcrumb">
                    @foreach ($parentDirs as $dir)
                        <a href="?dir={{ base64_encode($dir) }}"><li>{{ $dir }}</li></a>
                    @endforeach
                </ul>
            @endif

            <div class="list-group">
                @if ($parentDirPath && !$isCurrentDirectoryBase)
                    <a href="?dir={{ base64_encode($parentDirPath) }}"
                       class="list-group-item">Level up</a>
                @endif

                @foreach($dirItems as $item)
                    @if ($item->isFile)
                        <a href="?file={{ base64_encode($item->path) }}"
                           class="list-group-item{{ $currentFile === $item->path ? ' llv-active' : '' }}">
                            {{ $item->name }}
                        </a>
                    @elseif ($item->isDir)
                        <a href="?dir={{ base64_encode($item->path) }}"
                           class="list-group-item">
                            {{ "$item->name/" }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="col-sm-9 col-md-10 table-container">
            @if ($logs === null)
                <div>
                    File is too big, please download it.
                </div>
            @else
                <table id="table-log" class="table table-striped">
                    <thead>
                    <tr>
                        <th>Level</th>
                        <th>Context</th>
                        <th>Date</th>
                        <th>Content</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td class="text-{{ $log->levelClass }}}">
                                <span class="glyphicon glyphicon-{{ $log->levelImg }}-sign"
                                      aria-hidden="true"></span>
                                &nbsp;{{ $log->level }}
                            </td>
                            <td class="text">{{ $log->context }}</td>
                            <td class="date">{{ $log->date }}</td>
                            <td class="text">
                                {{ $log->text }}
                                @if ($log->inFile)
                                    <br>
                                    {{ $log->inFile }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif

            @if ($currentFile)
                <div>
                    <a href="?file={{ base64_encode($currentFile) }}&download=1">
                        <span class="glyphicon glyphicon-download-alt"></span>
                        Download file
                    </a>
                    -
                    <a id="delete-log"
                       href="?file={{ base64_encode($currentFile) }}&delete=1">
                        <span class="glyphicon glyphicon-trash"></span>
                        Delete file
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.10.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/9dcbecd42ad/integration/bootstrap/3/dataTables.bootstrap.js"></script>

<script>
    $(document).ready(function () {
        $('#table-log').DataTable({
            'order': [1, 'desc'],
            'stateSave': true,
            'stateSaveCallback': function (settings, data) {
                window.localStorage.setItem('datatable', JSON.stringify(data));
            },
            'stateLoadCallback': function (settings) {
                var data = JSON.parse(window.localStorage.getItem('datatable'));
                if (data) {
                    data.start = 0;
                }
                return data;
            }
        });
        $('.table-container').on('click', '.expand', function () {
            $('#' + $(this).data('display')).toggle();
        });
        $('#delete-log').click(function () {
            return confirm('Are you sure?');
        });
    });
</script>

</body>
</html>
