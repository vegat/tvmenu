<?php
$dataFile = __DIR__ . '/data/menu.json';
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([
        'updated_at' => microtime(true),
        'items' => []
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magiczna Kawiarenka - Menu</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;600;700&family=Pacifico&display=swap&family=Baloo+2:wght@600&display=swap" rel="stylesheet">
    <style>
        :root {
            --snow: #f7f9fb;
            --berry: #d7263d;
            --pine: #0d3b29;
            --gold: #f5c453;
            --cream: #fff9f3;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Fredoka', 'Baloo 2', sans-serif;
            background: linear-gradient(180deg, #0b132b, #1c2541);
            color: #fff;
            overflow: hidden;
        }
        .wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.08), transparent 30%),
                        radial-gradient(circle at 80% 10%, rgba(255,255,255,0.08), transparent 28%),
                        radial-gradient(circle at 50% 80%, rgba(255,255,255,0.05), transparent 25%);
            position: relative;
            overflow: hidden;
        }
        header {
            padding: 1.5rem 1.2rem 1rem;
            text-align: center;
        }
        header h1 {
            font-family: 'Pacifico', cursive;
            font-size: 2.8rem;
            margin: 0;
            color: var(--gold);
            text-shadow: 0 6px 16px rgba(0,0,0,0.4);
        }
        header p { margin: 0.3rem 0 0; font-size: 1.05rem; letter-spacing: 0.5px; color: #f8f1ff; }
        .menu-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
            padding: 0 1.4rem 2.6rem;
        }
        .menu-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }
        .menu-item {
            background: linear-gradient(145deg, rgba(255,255,255,0.1), rgba(255,255,255,0.22));
            border-radius: 18px;
            padding: 1.2rem 1.6rem;
            border: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 18px 34px rgba(0,0,0,0.28);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 1.4rem;
            min-height: 130px;
        }
        .menu-item::before {
            content: '';
            position: absolute;
            top: -30%; left: -30%;
            width: 70%; height: 70%;
            background: radial-gradient(circle, rgba(245,196,83,0.25), transparent 70%);
            transform: rotate(12deg);
        }
        .menu-item h3 {
            margin: 0 0 0.4rem 0;
            font-size: 2.1rem;
            letter-spacing: 0.8px;
        }
        .menu-item .price {
            font-size: 2.8rem;
            font-weight: 900;
            color: var(--gold);
            text-shadow: 0 10px 16px rgba(0,0,0,0.5);
            min-width: 170px;
            text-align: right;
        }
        .menu-item .desc {
            color: #f1e8ff;
            font-size: 1.1rem;
            margin-top: 0.2rem;
            min-height: 2.2rem;
        }
        .menu-item .content {
            flex: 1;
        }
        .snowflake {
            position: absolute;
            color: rgba(255,255,255,0.4);
            font-size: 1.2rem;
            animation: fall 12s linear infinite;
        }
        @keyframes fall {
            from { transform: translateY(-10vh) rotate(0deg); }
            to { transform: translateY(110vh) rotate(360deg); }
        }
        @keyframes float {
            0%,100% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
        }
        @keyframes wiggle {
            0% { transform: rotate(0deg) scale(1); }
            15% { transform: rotate(-4deg) scale(1.02); }
            30% { transform: rotate(4deg) scale(1.03); }
            45% { transform: rotate(-3deg) scale(1.02); }
            60% { transform: rotate(3deg) scale(1.02); }
            75% { transform: rotate(-2deg) scale(1.01); }
            100% { transform: rotate(0deg) scale(1); }
        }
        .carousel {
            flex: 1;
            position: relative;
        }
        #snow {
            position: absolute;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
        }
        .panel {
            position: absolute;
            inset: 0;
            transition: opacity 0.8s ease, transform 0.8s ease;
        }
        .panel.hidden { opacity: 0; pointer-events: none; transform: scale(0.98); }
        .panel.visible { opacity: 1; transform: scale(1); }
        .promo {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            background: linear-gradient(135deg, rgba(215,38,61,0.85), rgba(13,59,41,0.92));
            border-radius: 18px;
            box-shadow: inset 0 0 35px rgba(255,255,255,0.08);
        }
        .promo img {
            max-width: 90%;
            max-height: 90%;
            border: 10px dashed rgba(245,196,83,0.8);
            border-radius: 16px;
            background: rgba(255,255,255,0.12);
            box-shadow: 0 18px 28px rgba(0,0,0,0.35);
        }
        footer {
            text-align: center;
            padding: 0.5rem;
            color: #e8d8ff;
            font-size: 0.9rem;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.9rem;
            background: rgba(245,196,83,0.18);
            color: var(--gold);
            border-radius: 999px;
            font-weight: 700;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <header>
            <div class="badge">🎄 Magiczna Kawiarenka • Świąteczne menu</div>
            <h1>Magiczna Kawiarenka</h1>
            <p>Rozgrzewające napoje i słodkości prosto z zimowej krainy</p>
        </header>
        <div class="menu-container carousel">
            <div class="panel visible" id="menuPanel">
                <ul class="menu-list" id="menuList"></ul>
            </div>
            <div class="panel hidden" id="promoPanel">
                <div class="promo">
                    <img src="https://via.placeholder.com/900x1400.png?text=Magiczna+Promocja" alt="Promocja">
                </div>
            </div>
        </div>
        <footer>Menu odświeża się automatycznie, gdy dodasz nowe pozycje w panelu. ✨</footer>
        <div id="snow"></div>
    </div>

    <script>
        const menuList = document.getElementById('menuList');
        const menuPanel = document.getElementById('menuPanel');
        const promoPanel = document.getElementById('promoPanel');
        let lastUpdated = 0;
        let cycle = 0;

        function renderMenu(items) {
            menuList.innerHTML = '';
            items.forEach(item => {
                const li = document.createElement('li');
                li.className = 'menu-item';
                li.innerHTML = `
                    <div class="content">
                        <h3>${item.name}</h3>
                        <div class="desc">${item.description || ''}</div>
                    </div>
                    <div class="price">${item.price}</div>
                `;
                menuList.appendChild(li);
            });
            animateItems();
        }

        function animateItems() {
            const items = Array.from(document.querySelectorAll('.menu-item'));
            items.forEach((item, index) => {
                item.style.animation = 'pop 1s ease';
                item.style.animationDelay = `${index * 80}ms`;
            });
        }

        function playfulWiggle() {
            const items = Array.from(document.querySelectorAll('.menu-item'));
            items.forEach((item, index) => {
                item.classList.remove('wiggle');
                void item.offsetWidth;
                item.style.animationDelay = `${index * 60}ms`;
                item.classList.add('wiggle');
                setTimeout(() => item.classList.remove('wiggle'), 1100);
            });
        }

        async function fetchMenu(wait = false) {
            const url = new URL('api.php', window.location.href);
            url.searchParams.set('action', 'menu');
            if (wait) {
                url.searchParams.set('wait', '1');
                url.searchParams.set('since', lastUpdated);
            }
            const res = await fetch(url.toString());
            const data = await res.json();
            if (data.updated_at && data.updated_at !== lastUpdated) {
                lastUpdated = data.updated_at;
                renderMenu(data.items || []);
            }
            return data;
        }

        async function startLongPolling() {
            while (true) {
                try {
                    await fetchMenu(true);
                } catch (e) {
                    console.error('Błąd odświeżania', e);
                    await new Promise(r => setTimeout(r, 2000));
                }
            }
        }

        function scheduleCarousel() {
            setInterval(() => {
                cycle += 1;
                const showPromo = cycle % 4 === 0;
                if (showPromo) {
                    menuPanel.classList.add('hidden');
                    menuPanel.classList.remove('visible');
                    promoPanel.classList.remove('hidden');
                    promoPanel.classList.add('visible');
                    setTimeout(() => {
                        promoPanel.classList.add('hidden');
                        promoPanel.classList.remove('visible');
                        menuPanel.classList.remove('hidden');
                        menuPanel.classList.add('visible');
                    }, 4000);
                } else {
                    menuPanel.classList.add('visible');
                    menuPanel.classList.remove('hidden');
                    playfulWiggle();
                }
            }, 6500);
        }

        function createSnowflakes() {
            const snow = document.getElementById('snow');
            const symbols = ['❄', '✼', '❅', '✧'];
            for (let i = 0; i < 30; i++) {
                const span = document.createElement('span');
                span.className = 'snowflake';
                span.textContent = symbols[i % symbols.length];
                span.style.left = Math.random() * 100 + 'vw';
                span.style.animationDuration = 8 + Math.random() * 8 + 's';
                span.style.animationDelay = Math.random() * 10 + 's';
                snow.appendChild(span);
            }
        }

        const style = document.createElement('style');
        style.innerHTML = `
            @keyframes pop {
                0% { transform: scale(0.9) rotate(-2deg); opacity: 0; }
                60% { transform: scale(1.05) rotate(2deg); opacity: 1; }
                100% { transform: scale(1) rotate(0deg); }
            }
            .wiggle { animation: wiggle 1s ease; }
        `;
        document.head.appendChild(style);

        (async function init() {
            createSnowflakes();
            await fetchMenu(false);
            startLongPolling();
            scheduleCarousel();
        })();
    </script>
</body>
</html>
