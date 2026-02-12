<!DOCTYPE html>
<html lang="en">
<head>
    <title>AirAware | Palestine Weather Intelligence</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --teal: #0d9488;
            --teal-light: #2dd4bf;
            --bg-light: #f8fafc;
            --text-dark: #0f172a;
            --glass-white: rgba(255, 255, 255, 0.85);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0fdfa 0%, #e2e8f0 100%);
            min-height: 100vh;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .main-wrapper {
            width: 100%;
            max-width: 1100px;
            background: var(--glass-white);
            backdrop-filter: blur(15px);
            border: 2px solid #ffffff;
            border-radius: 40px;
            padding: 40px;
            box-shadow: 0 40px 80px rgba(13, 148, 136, 0.1);
        }

        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .logo-area { display: flex; align-items: center; gap: 15px; }
        .logo-img { height: 50px; width: auto; border-radius: 12px; }
        .brand-name { font-size: 28px; font-weight: 800; color: var(--teal); letter-spacing: -1px; }

        .dashboard {
            display: grid;
            grid-template-columns: 1.7fr 1fr;
            gap: 30px;
        }

        #map {
            height: 500px;
            width: 100%;
            border-radius: 30px;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: inset 0 0 20px rgba(0,0,0,0.05);
        }

        .side-panel { display: flex; flex-direction: column; gap: 20px; }

        .card {
            background: white;
            border-radius: 25px;
            padding: 25px;
            border: 1px solid rgba(13, 148, 136, 0.1);
            box-shadow: 0 10px 20px rgba(0,0,0,0.02);
        }

        .card h3 { margin: 0 0 15px 0; font-size: 18px; color: var(--teal); }
        
        .city-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .city-item:last-child { border: none; }
        .city-name { font-weight: 600; font-size: 15px; }
        .city-temp { 
            background: #f0fdfa; 
            color: var(--teal); 
            padding: 4px 12px; 
            border-radius: 10px; 
            font-weight: 800; 
        }

        /* Teal Glowing Marker */
        .teal-marker {
            width: 18px;
            height: 18px;
            background: var(--teal);
            border: 3px solid white;
            border-radius: 50%;
            box-shadow: 0 0 15px var(--teal-light);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(13, 148, 136, 0.4); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 10px rgba(13, 148, 136, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(13, 148, 136, 0); }
        }

        @media(max-width: 900px) {
            .dashboard { grid-template-columns: 1fr; }
            #map { height: 350px; }
        }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="top-nav">
        <div class="logo-area">
            <img src="{{ asset('logo.png') }}" alt="AirAware Logo" class="logo-img">
            <span class="brand-name">AIRAWARE</span>
        </div>
        <div style="text-align: right; opacity: 0.6; font-size: 12px;">
            <b>Palestine Network</b><br>
            {{ date('l, F j') }}
        </div>
    </div>

    <div class="dashboard">
        <div id="map"></div>

        <div class="side-panel">
            <div class="card">
    <h3>Regional Summary</h3>
    <div style="font-size: 14px; color: #64748b;">
        <p>Status: 
            <span style="color: {{ $efficiency > 0 ? '#10b981' : '#ef4444' }}; font-weight: bold;">
                ● {{ $efficiency > 0 ? 'Active' : 'Offline' }}
            </span>
        </p>
        <p>Nodes Detected: <b>{{ count($weatherData) }} / 4</b></p>
        
        <div style="height: 8px; background: #f1f5f9; border-radius: 10px; margin-top: 15px; overflow: hidden;">
            <div style="width: {{ $efficiency }}%; height: 100%; background: var(--teal); border-radius: 10px; transition: width 1s ease-in-out;"></div>
        </div>
        
        <p style="font-size: 11px; margin-top: 8px;">Network Efficiency: <b>{{ $efficiency }}%</b></p>
    </div>
</div>

            <div class="card">
                <h3>Live Feed</h3>
                <div id="city-list">
                    </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    // Silver/Light Professional Map style
    var map = L.map('map', { zoomControl: false }).setView([32.0, 35.1], 8);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    var citiesWeather = @json($weatherData);
    var listContainer = document.getElementById('city-list');

    if (citiesWeather && citiesWeather.length > 0) {
        citiesWeather.forEach(function(city) {
            var data = city.current_weather;
            
            // Add to the Sidebar List
            listContainer.innerHTML += `
                <div class="city-item">
                    <span class="city-name">${city.city_name}</span>
                    <span class="city-temp">${data.temperature}°C</span>
                </div>
            `;

            // Custom Teal Glowing Marker
            var tealIcon = L.divIcon({
                className: 'custom-div-icon',
                html: `<div class="teal-marker"></div>`,
                iconSize: [18, 18],
                iconAnchor: [9, 9]
            });

            L.marker([city.latitude, city.longitude], {icon: tealIcon})
                .addTo(map)
                .bindPopup(`
                    <div style="color: #0f172a; font-family: 'Inter'; text-align: center;">
                        <b style="font-size: 15px;">${city.city_name}</b><br>
                        <span style="font-size: 20px; font-weight: 800; color: #0d9488;">${data.temperature}°C</span><br>
                        <small style="opacity: 0.7;">Wind: ${data.windspeed} km/h</small>
                    </div>
                `);
        });
    }
</script>

</body>
</html>