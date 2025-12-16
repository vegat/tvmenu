<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administracja - Magiczna Kawiarenka</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;600;700&family=Pacifico&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Fredoka', sans-serif;
            background: radial-gradient(circle at 20% 20%, #fff6f1, #f7dede);
            margin: 0;
            color: #2b0f28;
        }
        header {
            background: linear-gradient(120deg, #8a1538, #c52b5f);
            color: #fff;
            padding: 1.5rem 2rem;
            box-shadow: 0 8px 18px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        header h1 {
            margin: 0;
            font-family: 'Pacifico', cursive;
            letter-spacing: 1px;
            font-size: 1.9rem;
        }
        main {
            padding: 1.5rem;
            max-width: 1100px;
            margin: 0 auto;
        }
        .card {
            background: #fff;
            border-radius: 14px;
            padding: 1.2rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        label { display:block; margin-top: 0.8rem; font-weight: 600; }
        input, textarea {
            width: 100%;
            padding: 0.7rem;
            border-radius: 10px;
            border: 1px solid #e1c6c6;
            font-size: 1rem;
            font-family: 'Fredoka', sans-serif;
            box-sizing: border-box;
        }
        button {
            background: linear-gradient(120deg, #e63946, #ff8fa3);
            border: none;
            padding: 0.8rem 1.3rem;
            color: #fff;
            font-weight: 700;
            border-radius: 12px;
            cursor: pointer;
            margin-top: 1rem;
            box-shadow: 0 8px 16px rgba(230,57,70,0.35);
            transition: transform 0.15s ease, box-shadow 0.2s ease;
        }
        button:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(230,57,70,0.35); }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 0.75rem 0.5rem;
            text-align: left;
        }
        th { color: #7d4f50; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
        tbody tr { border-bottom: 1px solid #f0d5d5; }
        .pill {
            padding: 0.35rem 0.8rem;
            border-radius: 20px;
            font-weight: 700;
            display: inline-block;
            font-size: 0.85rem;
        }
        .pill.active { background: #e2f6e9; color: #1b5e20; }
        .pill.inactive { background: #ffe5e5; color: #9a1f40; }
        .actions button { margin-right: 0.5rem; }
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #2b0f28;
            color: #fff;
            padding: 0.9rem 1.2rem;
            border-radius: 12px;
            box-shadow: 0 15px 25px rgba(0,0,0,0.2);
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        .toast.show { opacity: 1; transform: translateY(0); }
    </style>
</head>
<body>
    <header>
        <h1>Magiczna Kawiarenka • Panel</h1>
        <div>Dodawaj, wstrzymuj i usuwaj pozycje w locie ✨</div>
    </header>
    <main>
        <section class="card">
            <h2>Dodaj nową pozycję</h2>
            <label for="name">Nazwa*</label>
            <input type="text" id="name" placeholder="Gorąca czekolada z piankami">
            <label for="price">Cena*</label>
            <input type="text" id="price" placeholder="22 zł">
            <label for="description">Opis (opcjonalnie)</label>
            <textarea id="description" rows="2" placeholder="Z kardamonem, cynamonem i puszystą bitą śmietaną"></textarea>
            <button id="addBtn">Dodaj pozycję</button>
        </section>
        <section class="card">
            <h2>Twoje pozycje</h2>
            <table>
                <thead>
                    <tr><th>Nazwa</th><th>Cena</th><th>Opis</th><th>Status</th><th>Akcje</th></tr>
                </thead>
                <tbody id="menuTable"></tbody>
            </table>
        </section>
    </main>
    <div class="toast" id="toast"></div>

    <script>
        async function fetchMenu() {
            const res = await fetch('api.php?action=list');
            return res.json();
        }

        function formatPriceDisplay(value) {
            const raw = String(value ?? '').trim();
            const numberMatch = raw.match(/[0-9]+(?:[.,][0-9]+)?/);
            const currencyMatch = raw.match(/[^\d.,\s]+/);
            const currency = (currencyMatch ? currencyMatch[0] : 'PLN').toUpperCase();
            const numeric = numberMatch ? numberMatch[0].replace(',', '.') : '0';
            const parsed = parseFloat(numeric);
            const amount = Number.isFinite(parsed) ? (Number.isInteger(parsed) ? parsed.toString() : parsed.toFixed(2).replace(/\.00$/, '')) : raw;
            return `${amount} ${currency}`;
        }

        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2500);
        }

        function renderTable(data) {
            const tbody = document.getElementById('menuTable');
            tbody.innerHTML = '';
            data.items.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.name}</td>
                    <td><strong>${formatPriceDisplay(item.price)}</strong></td>
                    <td>${item.description || ''}</td>
                    <td><span class="pill ${item.active ? 'active' : 'inactive'}">${item.active ? 'Aktywna' : 'Wstrzymana'}</span></td>
                    <td class="actions">
                        <button onclick="toggleItem('${item.id}', ${!item.active})">${item.active ? 'Deaktywuj' : 'Aktywuj'}</button>
                        <button style="background:#ffd166;color:#2b0f28" onclick="deleteItem('${item.id}')">Usuń</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        async function addItem() {
            const payload = {
                name: document.getElementById('name').value,
                price: document.getElementById('price').value,
                description: document.getElementById('description').value
            };
            const res = await fetch('api.php?action=add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (!res.ok) return showToast(data.error || 'Błąd');
            showToast('Dodano pozycję');
            document.getElementById('name').value = '';
            document.getElementById('price').value = '';
            document.getElementById('description').value = '';
            refresh();
        }

        async function toggleItem(id, active) {
            const res = await fetch('api.php?action=toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, active })
            });
            const data = await res.json();
            if (!res.ok) return showToast(data.error || 'Błąd');
            showToast('Zmieniono status');
            refresh();
        }

        async function deleteItem(id) {
            if (!confirm('Czy na pewno usunąć?')) return;
            const res = await fetch('api.php?action=delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (!res.ok) return showToast(data.error || 'Błąd');
            showToast('Usunięto pozycję');
            refresh();
        }

        async function refresh() {
            const data = await fetchMenu();
            renderTable(data);
        }

        document.getElementById('addBtn').addEventListener('click', addItem);
        refresh();
    </script>
</body>
</html>
