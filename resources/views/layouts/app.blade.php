<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="http://172.22.118.101:81/proyectsImages/favicon_color.png?raw=true" type="image/x-icon">
    <style>
        body {
            background-color: #003764;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background-color: #003764;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-container img {
            max-height: 200px;
            width: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4 col-sm-6">
                <div class="login-container p-4">
                    <div class="logo-container">
                        <img src="http://172.22.118.101:81/proyectsImages/logo ranking.png"
                            alt="Logo de la Empresa" class="img-fluid">
                    </div>
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</body>
</html>
