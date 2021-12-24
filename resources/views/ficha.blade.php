<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Ficha - {{$data['Nome'] ?? ''}}</title>
    <!-- BootStrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <!-- Fonts -->

    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <!-- Styles -->
    <style>
        @media print
        {
            .no-print, .no-print *
            {
                display: none !important;
            }
        }
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }


    </style>
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-center">
        <img src="https://toodobe.herokuapp.com/logo-toodobe.png" style="max-width: 300px;margin-top: 80px;margin-bottom: 80px;">
    </div>
    <button type="button" class="btn btn-warning no-print" onclick="window.print()" style="
    text-align: center;
    margin: auto;
    display: block;
    margin-bottom: 30px;
">Imprimir</button>

    <table class="table table-striped">
        <thead>
        <tr>
            <div class="d-flex justify-content-center">
            <h3>DADOS CADASTRAIS</h3>
            </div>
        </tr>
        </thead>
        <tbody>

        @foreach($data as $key => $value)
            <tr>
                <th>
                    <strong>{{$key}}</strong>
                </th>
                <td>
                    @if(is_array($value))
                        @foreach($value as $item)
                        <p>{{$item}}</p>
                        @endforeach
                    @else
                        {{$value}}
                    @endif
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>
</div>
</body>
</html>
