<?php
header("refresh:12;url=/pages/sign-in.html");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>IMSSight :: UEI</title>

<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#000;
    overflow:hidden;
    height:100vh;
    font-family:'Orbitron', sans-serif;
    display:flex;
    justify-content:center;
    align-items:center;
    color:#8fffe0;
}

/* Fondo estilo terminal */
body::before{
    content:"";
    position:absolute;
    width:100%;
    height:100%;
    background:
        radial-gradient(circle at center, rgba(20,90,74,0.25), transparent 70%),
        repeating-linear-gradient(
            to bottom,
            rgba(255,255,255,0.02),
            rgba(255,255,255,0.02) 1px,
            transparent 1px,
            transparent 3px
        );
    animation:flicker 0.15s infinite;
    pointer-events:none;
}

@keyframes flicker{
    0%{opacity:0.97;}
    50%{opacity:1;}
    100%{opacity:0.96;}
}

.container{
    text-align:center;
    z-index:2;
    width:90%;
    max-width:1100px;
}

.logo{
    font-size:5rem;
    font-weight:800;
    letter-spacing:8px;
    color:#58f5c8;
    text-shadow:
        0 0 10px #58f5c8,
        0 0 20px #58f5c8,
        0 0 40px #145A4A;
    
    animation:pulse 2s infinite;
}

@keyframes pulse{
    0%{opacity:0.7;}
    50%{opacity:1;}
    100%{opacity:0.7;}
}

.subtitle{
    margin-top:15px;
    font-size:1rem;
    color:#9aa0a6;
    letter-spacing:3px;
}

.system{
    margin-top:70px;
    border:1px solid rgba(88,245,200,0.3);
    padding:30px;
    background:rgba(0,0,0,0.55);
    box-shadow:
        0 0 20px rgba(88,245,200,0.15),
        inset 0 0 20px rgba(88,245,200,0.05);
    backdrop-filter:blur(4px);
}

.line{
    font-size:1.3rem;
    margin:12px 0;
    opacity:0;
    animation:fadeIn 1s forwards;
}

.line:nth-child(1){
    animation-delay:0.5s;
}

.line:nth-child(2){
    animation-delay:2s;
}

.line:nth-child(3){
    animation-delay:4s;
}

.line:nth-child(4){
    animation-delay:6s;
}

@keyframes fadeIn{
    from{
        opacity:0;
        transform:translateY(10px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}

.green{
    color:#58f5c8;
}

.warning{
    color:#BC955C;
}

.danger{
    color:#DC3545;
}

.footer{
    margin-top:40px;
    font-size:0.8rem;
    color:#666;
    letter-spacing:2px;
}

.loading{
    margin-top:25px;
    height:4px;
    width:100%;
    background:#111;
    overflow:hidden;
    position:relative;
}

.loading::after{
    content:"";
    position:absolute;
    left:-40%;
    width:40%;
    height:100%;
    background:#58f5c8;
    box-shadow:0 0 15px #58f5c8;
    animation:loading 10s linear forwards;
}

@keyframes loading{
    from{
        left:-40%;
    }
    to{
        left:100%;
    }
}

.hex{
    position:absolute;
    width:300px;
    height:300px;
    border:1px solid rgba(88,245,200,0.08);
    transform:rotate(45deg);
    animation:rotate 20s linear infinite;
}

.hex:nth-child(1){
    width:500px;
    height:500px;
}

.hex:nth-child(2){
    width:700px;
    height:700px;
    animation-direction:reverse;
}

@keyframes rotate{
    from{
        transform:rotate(0deg);
    }
    to{
        transform:rotate(360deg);
    }
}

</style>
</head>

<body>

<div class="hex"></div>
<div class="hex"></div>

<div class="container">

    <div class="logo">
        IMSSIGHT
    </div>

    <div class="subtitle">
        Conocimiento clínico a primera vista
    </div>

    <div class="system">

        <div class="line green">
            TODOS LOS SISTEMAS FUNCIONANDO DENTRO DE LOS PARÁMETROS NORMALES
        </div>

        <div class="line">
            CAMPO DE CONTENCIÓN AL MÁXIMO.
        </div>

        <div class="line">
            UNIDAD DE OEI, FAVOR DE REPORTARSE A LAS CÁMARAS DE DISECCIÓN DE P-C-R-A. 
            <br>
            BRECHA EN PROGRESO.
        </div>

        <div class="line warning">
            Autorización de seguridad nivel Delta 5 aprobada.
        </div>
        
        <div class="loading"></div>

    </div>

    <div class="footer">
        INITIALIZING CLINICAL SIMULATION ENVIRONMENT...
    </div>

</div>

</body>
</html>