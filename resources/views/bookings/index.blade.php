<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking History</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .section { margin-bottom: 24px; }
        .booking { border: 1px solid #e0e0e0; padding: 12px; margin-bottom: 8px; border-radius: 6px; display:flex; justify-content:space-between; align-items:center }
        .meta { color: #444; }
        .status { padding: 4px 8px; border-radius: 4px; font-weight:600 }
        .status.pending { background:#fff3cd; color:#856404 }
        .status.confirmed { background:#d4edda; color:#155724 }
        .status.canceled { background:#f8d7da; color:#721c24 }
        #detailsModal { position:fixed; inset:0; display:none; align-items:center; justify-content:center; background:rgba(0,0,0,.4) }
        #detailsModal .panel { background:#fff; padding:20px; width:90%; max-width:720px; border-radius:8px }
        button { cursor:pointer }
    </style>
</head>
<body>
    <h1>Booking History</h1>

    <div class="section">
        <h2>Upcoming</h2>
        <div id="upcomingList">Loading...</div>
    </div>

    <div class="section">
        <h2>Past</h2>
        <div id="pastList">Loading...</div>
    </div>

    <div id="detailsModal">
        <div class="panel">
            <h3 id="detailTitle">Booking details</h3>
            <div id="detailBody">Loading...</div>
            <div style="text-align:right; margin-top:12px">
                <button id="closeDetails">Close</button>
            </div>
        </div>
    </div>

    <script>
        function formatDate(dtStr) {
            const d = new Date(dtStr);
            return d.toLocaleString();
        }

        function renderBooking(b) {
            const container = document.createElement('div');
            container.className = 'booking';

            const left = document.createElement('div');
            left.innerHTML = `<div><strong>${b.service ? b.service.name : '—'}</strong></div>` +
                             `<div class="meta">${formatDate(b.booking_start)} — ${formatDate(b.booking_end)}</div>`;

            const right = document.createElement('div');
            const status = document.createElement('span');
            status.className = 'status ' + (b.status || 'pending');
            status.textContent = b.status || 'pending';

            const detailsBtn = document.createElement('button');
            detailsBtn.textContent = 'Details';
            detailsBtn.style.marginLeft = '12px';
            detailsBtn.onclick = () => showDetails(b);

            right.appendChild(status);
            right.appendChild(detailsBtn);

            container.appendChild(left);
            container.appendChild(right);

            return container;
        }

        function showDetails(b) {
            const modal = document.getElementById('detailsModal');
            document.getElementById('detailTitle').textContent = (b.service ? b.service.name : 'Booking') + ' — ' + (b.tradie ? b.tradie.name : '');
            const html = `
                <p><strong>Service:</strong> ${b.service ? b.service.name : '—'}</p>
                <p><strong>Tradie:</strong> ${b.tradie ? b.tradie.name : '—'}</p>
                <p><strong>Start:</strong> ${formatDate(b.booking_start)}</p>
                <p><strong>End:</strong> ${formatDate(b.booking_end)}</p>
                <p><strong>Status:</strong> ${b.status}</p>
                <p><strong>Total price:</strong> ${b.total_price ? ('$' + b.total_price) : '—'}</p>
                <h4>Logs</h4>
                <div id="logs">${(b.logs && b.logs.length) ? b.logs.map(l => `<div>${new Date(l.created_at).toLocaleString()} — ${l.action} — ${l.notes || ''}</div>`).join('') : '<div>No logs</div>'}</div>
            `;
            document.getElementById('detailBody').innerHTML = html;
            modal.style.display = 'flex';
        }

        document.getElementById('closeDetails').addEventListener('click', () => {
            document.getElementById('detailsModal').style.display = 'none';
        });

        // Fetch the grouped bookings and render
        document.addEventListener('DOMContentLoaded', function() {
            fetch('/api/bookings/history', { credentials: 'same-origin' })
                .then(r => {
                    if (!r.ok) throw new Error('Failed to load bookings');
                    return r.json();
                })
                .then(data => {
                    const up = document.getElementById('upcomingList');
                    const past = document.getElementById('pastList');
                    up.innerHTML = '';
                    past.innerHTML = '';

                    if (data.upcoming && data.upcoming.length) {
                        data.upcoming.forEach(b => up.appendChild(renderBooking(b)));
                    } else {
                        up.innerHTML = '<div>No upcoming bookings</div>';
                    }

                    if (data.past && data.past.length) {
                        data.past.forEach(b => past.appendChild(renderBooking(b)));
                    } else {
                        past.innerHTML = '<div>No past bookings</div>';
                    }
                })
                .catch(err => {
                    document.getElementById('upcomingList').textContent = 'Failed to load';
                    document.getElementById('pastList').textContent = 'Failed to load';
                    console.error(err);
                });
        });
    </script>
</body>
</html>
