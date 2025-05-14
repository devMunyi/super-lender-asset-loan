<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();
$full_name = $userd['full_name'];
$names = explode(" ", $full_name);
$first_name = $names[0];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Holiday Orbit</title>

    <!-- Google Font for Calligraphy -->
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #000;
            font-family: sans-serif;
            color: #fff;
            overflow: hidden;
        }

        #holidayMessage {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            color: #fff;
            text-align: center;
            transition: transform 1s ease-in-out, opacity 1s ease-in-out;
            opacity: 0;
            z-index: 10;
            pointer-events: none;
            text-shadow: 0 0 10px red;
        }

        #holidayMessage.show {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }

        #holidayMessage .line1 {
            font-size: 1.5em;
            margin-bottom: 0.5em;
            font-family: sans-serif;
        }

        #holidayMessage .line2 {
            font-size: 2.5em;
            font-family: 'Great Vibes', cursive;
            font-weight: normal;
        }

        #holidayMessage::before {
            content: "🎄🎅❄️";
            display: block;
            font-size: 3em;
            margin-bottom: 0.2em;
        }

        #holidayMessage::after {
            content: "🎄🎅❄️";
            display: block;
            font-size: 3em;
            margin-top: 0.2em;
        }

    </style>
</head>
<body>
<canvas id="orbitCanvas"></canvas>
<div id="holidayMessage">
    <div class="line1">Another Year Around the Sun</div>
    <div class="line2">Happy Holidays <?php echo $first_name; ?></div>
</div>

<script>
    const canvas = document.getElementById("orbitCanvas");
    const ctx = canvas.getContext("2d");

    function resizeCanvas() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    const sunX = canvas.width / 2;
    const sunY = canvas.height / 2;
    const sunRadius = 50;

    // Planet parameters
    // orbitRadius, radius, speed (orbit speed), color or gradient setup
    const planets = [
        {
            name: "Mercury",
            orbitRadius: Math.min(canvas.width, canvas.height)/8,
            radius: 6,
            speed: 0.02,
            color: "#aaa",
            angle: 0
        },
        {
            name: "Venus",
            orbitRadius: Math.min(canvas.width, canvas.height)/6.5,
            radius: 9,
            speed: 0.015,
            color: "#d4aa00",
            angle: 0
        },
        {
            name: "Earth",
            orbitRadius: Math.min(canvas.width, canvas.height)/4,
            radius: 15,
            speed: 0.01,
            isEarth: true, // to draw continents and special gradient
            angle: 0
        },
        {
            name: "Mars",
            orbitRadius: Math.min(canvas.width, canvas.height)/3.2,
            radius: 10,
            speed: 0.008,
            color: "#c1440e",
            angle: 0
        }
    ];

    // Flags & Timing
    let orbiting = true;
    const showMessageTime = 5000; // time in ms to show the message
    const stopOrbitTime = 8000;   // time in ms after which planets stop orbiting

    // Snowflake parameters (falling & settling)
    const numFlakes = 100;
    const fallingFlakes = [];
    const settledFlakes = [];

    for (let i = 0; i < numFlakes; i++) {
        fallingFlakes.push(createFlake());
    }

    function createFlake() {
        const size = Math.random() * 18 + 2; // 2px to 20px
        return {
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            size: size,
            d: (Math.random() * 0.5 + 0.5) * (size / 10), // speed factor depends on size
        };
    }

    function drawScene() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        drawSun();
        drawPlanets();
        drawSnow();

        requestAnimationFrame(drawScene);
    }

    function drawSun() {
        let sunGrad = ctx.createRadialGradient(sunX, sunY, 0, sunX, sunY, sunRadius);
        sunGrad.addColorStop(0, '#fff9b1');
        sunGrad.addColorStop(0.5, '#ffe800');
        sunGrad.addColorStop(1, '#ff8c00');

        ctx.beginPath();
        ctx.arc(sunX, sunY, sunRadius, 0, 2 * Math.PI);
        ctx.fillStyle = sunGrad;
        ctx.fill();
    }

    function drawPlanets() {
        for (let p of planets) {
            if (orbiting) {
                p.angle += p.speed;
            }

            const px = sunX + p.orbitRadius * Math.cos(p.angle);
            const py = sunY + p.orbitRadius * Math.sin(p.angle);

            ctx.save();

            if (p.isEarth) {
                // Earth gradient
                let earthGrad = ctx.createRadialGradient(px, py, 0, px, py, p.radius);
                earthGrad.addColorStop(0, '#aadeff');
                earthGrad.addColorStop(1, '#0051a3');

                ctx.beginPath();
                ctx.arc(px, py, p.radius, 0, 2*Math.PI);
                ctx.fillStyle = earthGrad;
                ctx.fill();

                // Simple continents
                ctx.fillStyle = "#3cb371";
                ctx.beginPath();
                ctx.arc(px - p.radius/3, py - p.radius/4, p.radius/3, 0, Math.PI * 1.5);
                ctx.fill();

                ctx.beginPath();
                ctx.arc(px + p.radius/4, py + p.radius/6, p.radius/4, Math.PI/2, Math.PI*2);
                ctx.fill();

            } else {
                // For other planets, just a colored circle
                ctx.beginPath();
                ctx.arc(px, py, p.radius, 0, 2 * Math.PI);
                ctx.fillStyle = p.color;
                ctx.fill();
            }

            ctx.restore();
        }
    }

    function drawSnow() {
        // Draw settled flakes first
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.fillStyle = "#FFF";
        for (let f of settledFlakes) {
            ctx.font = f.size + "px Arial";
            ctx.fillText("❄", f.x, f.y);
        }

        // Draw and update falling flakes
        for (let i = fallingFlakes.length - 1; i >= 0; i--) {
            const f = fallingFlakes[i];
            ctx.font = f.size + "px Arial";
            ctx.fillStyle = "#FFF";
            ctx.fillText("❄", f.x, f.y);

            // Update position
            f.y += f.d;

            // If the flake hits the bottom, settle it
            if (f.y > canvas.height - f.size * 0.5) {
                f.y = canvas.height - f.size * 0.5; // place it right at bottom
                settledFlakes.push(f);
                fallingFlakes.splice(i, 1);

                // Add a new flake at the top to keep it snowing
                fallingFlakes.push(createFlake());
            }
        }
    }

    // Start the animation
    requestAnimationFrame(drawScene);

    // Show the holiday message after 5s
    setTimeout(() => {
        const holidayMessage = document.getElementById("holidayMessage");
        holidayMessage.classList.add("show");
    }, showMessageTime);

    // Stop orbiting planets after 8s
    setTimeout(() => {
        orbiting = false;
    }, stopOrbitTime);
</script>
</body>
</html>

