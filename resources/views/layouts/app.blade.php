<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>

        body{
            overflow-x:hidden;
            background:#f4f6f9;
        }

        .sidebar{
            width:260px;
            height:100vh;
            position:fixed;
            left:-260px;
            top:0;
            background:#0f172a;
            transition:0.4s;
            z-index:999;
            padding-top:20px;
        }

        .sidebar.active{
            left:0;
        }

        .sidebar a{
            color:white;
            text-decoration:none;
            display:block;
            padding:15px 25px;
            transition:0.3s;
        }

        .sidebar a:hover{
            background:#1e293b;
        }

        .main-content{
            transition:0.4s;
            padding:20px;
        }

        .topbar{
            background:white;
            padding:15px;
            border-radius:12px;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
            margin-bottom:20px;
        }

        .card-box{
            border:none;
            border-radius:18px;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
        }

    </style>
</head>
<body>

@include('layouts.sidebar')

<div class="main-content" id="mainContent">

    @include('layouts.navbar')

    @yield('content')

</div>

<script>

    const toggleBtn = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });

</script>

</body>
</html>