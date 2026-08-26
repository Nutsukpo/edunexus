<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title') | EDUNEXUS Student Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    <style>

        body{

            background:#f5f7fb;

            font-family:Nunito,sans-serif;

        }

        .content{

            margin-left:260px;

            padding:30px;

        }

        @media(max-width:992px){

            .content{

                margin-left:0;

            }

        }

    </style>

    @stack('styles')

</head>

<body>

@include('students.layouts.sidebar')

<div class="content">

@include('students.layouts.navbar')

@yield('content')

@include('students.layouts.footer')

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')

</body>
</html>