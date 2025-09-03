<?php
require '../vendor/autoload.php';
use App\Database\DB;

session_start();

$db = (new DB())->getConnection();
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Flights</title>
<?php include 'navbar.php'; ?>
<link rel="stylesheet" href="styles/catppuccin.css">
</head>
<body>
<div class="container">
    <header>
        <div class="title">
            <span class="badge">VBIS</span>
            <div>
                <h1>Flight Data</h1>
                <div class="subtitle">Flight information</div>
                
            </div>
            
        </div><?php if($isAdmin): ?>
<section class="card">
    <h2>Import Flights from API</h2>
    <form id="importApiForm">
        <div class="filters" style="grid-template-columns: 1fr auto; gap:10px;">
            <input type="number" name="count" id="apiCount" min="1" max="100" value="30" placeholder="Number of flights" required>
            <button class="clear-btn" type="submit">Import from API</button>
            
                <div class="subtitle">WARNING: Currently operating on 100 API calls/month</div>
        </div>
    </form>
</section>
<?php endif; ?>
        
    </header>

<?php if ($isAdmin): ?>
<section class="card">
    <h2>Add New Flight</h2>
    <form id="addFlightForm">
        <div class="filters" style="grid-template-columns: repeat(7, 1fr); gap:10px;">
            <input type="text" name="flight_number" placeholder="Flight #" required>
            <input type="text" name="airline" placeholder="Airline" required>
            <input type="text" name="departure_airport" placeholder="Departure" required>
            <input type="text" name="arrival_airport" placeholder="Arrival" required>
            <input type="datetime-local" name="departure_time" required>
            <input type="datetime-local" name="arrival_time" required>
            <select name="status" required>
                <option value="">Status</option>
                <option value="scheduled">Scheduled</option>
                <option value="active">Active</option>
                <option value="landed">Landed</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <button class="clear-btn" type="submit" style="grid-column: span 7;">Add Flight</button>
        </div>
    </form>
</section>
<?php endif; ?>

<section class="card">
    <div class="filters">
        <div class="control">
            <label for="airline">Airline</label>
            <input type="text" id="airline" placeholder="e.g. Lufthansa, Air Serbia" />
        </div>
        <div class="control">
            <label for="departure">Departure airport</label>
            <input type="text" id="departure" placeholder="e.g. Frankfurt, Belgrade" />
        </div>
        <div class="control">
            <label for="status">Status</label>
            <select id="status">
                <option value="">All</option>
                <option value="scheduled">Scheduled</option>
                <option value="active">Active</option>
                <option value="landed">Landed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <button class="clear-btn" id="clear">Clear filters</button>
    </div>

    <div class="table-wrap" id="tableWrap">
        <table>
            <thead>
                <tr>
                    <th>Flight</th>
                    <th>Airline</th>
                    <th>Departure</th>
                    <th>Departure Time</th>
                    <th>Arrival</th>
                    <th>Arrival Time</th>
                    <th>Status</th>
                    <?php if($isAdmin): ?><th>Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody id="flights-table">
                <tr><td colspan="<?= $isAdmin ? 8 : 7 ?>" class="empty">Loading…</td></tr>
            </tbody>
        </table>
    </div>

    <div id="pagination" style="margin-top:12px; display:flex; gap:6px; flex-wrap:wrap;"></div>
    <div class="meta">
        <div><span class="dot"></span>Showing latest results (50 per page)</div>
    </div>
</section>
</div>

<script>
const isAdmin = <?= $isAdmin ? 'true' : 'false'; ?>;
let currentPage = 1;

function debounce(fn, delay=300){let t; return (...args)=>{clearTimeout(t); t=setTimeout(()=>fn.apply(this,args), delay);};}

const airlineInput = document.getElementById('airline');
const departureInput = document.getElementById('departure');
const statusSelect = document.getElementById('status');
const clearBtn = document.getElementById('clear');
const tableBody = document.getElementById('flights-table');
const paginationDiv = document.getElementById('pagination');

function statusBadge(status){
    const cls = (status||'').toLowerCase();
    return `<span class="status ${cls}">${status || '—'}</span>`;
}

function showSkeleton(rows=6){
    tableBody.innerHTML='';
    for(let i=0;i<rows;i++){
        tableBody.innerHTML+=`
        <tr>
            <td><div class="skeleton" style="width:90px"></div></td>
            <td><div class="skeleton" style="width:180px"></div></td>
            <td><div class="skeleton" style="width:160px"></div></td>
            <td><div class="skeleton" style="width:120px"></div></td>
            <td><div class="skeleton" style="width:160px"></div></td>
            <td><div class="skeleton" style="width:120px"></div></td>
            <td><div class="skeleton" style="width:90px"></div></td>
            ${isAdmin ? '<td><div class="skeleton" style="width:80px"></div></td>' : ''}
        </tr>`;
    }
}

function formatTime(dt){
    if(!dt) return '—';
    const d = new Date(dt);
    return d.toLocaleString(undefined,{year:'numeric',month:'2-digit',day:'2-digit',hour:'2-digit',minute:'2-digit'});
}

function fetchFlights(page = 1){
    showSkeleton(5);
    currentPage = page;
    const params = new URLSearchParams({
        airline: airlineInput.value || '',
        departure: departureInput.value || '',
        status: statusSelect.value || '',
        page
    });

    fetch('fetchFlights.php?' + params.toString())
        .then(r => r.json())
        .then(data => {
            tableBody.innerHTML='';
            if(!data.flights || !data.flights.length){
                tableBody.innerHTML=`<tr><td colspan="${isAdmin ? 8 : 7}" class="empty">No flights found</td></tr>`;
                paginationDiv.innerHTML = '';
                return;
            }

            const rows = data.flights.map(f => `
                <tr>
                    <td>${escapeHtml(f.flight_number||'')}</td>
                    <td>${escapeHtml(f.airline||'')}</td>
                    <td>${escapeHtml(f.departure_airport||'')}</td>
                    <td>${escapeHtml(formatTime(f.departure_time))}</td>
                    <td>${escapeHtml(f.arrival_airport||'')}</td>
                    <td>${escapeHtml(formatTime(f.arrival_time))}</td>
                    <td>${statusBadge(f.status)}</td>
                    ${isAdmin ? `<td><button class="clear-btn delete-btn" data-id="${f.id}">Delete</button></td>` : ''}
                </tr>
            `).join('');
            tableBody.innerHTML = rows;

            // pagination buttons
            let pagesHtml = '';
            for(let i=1; i<=data.pages; i++){
                pagesHtml += `<button class="page-btn ${i===currentPage?'active':''}" data-page="${i}">${i}</button>`;
            }
            paginationDiv.innerHTML = pagesHtml;
            document.querySelectorAll('.page-btn').forEach(btn => {
                btn.addEventListener('click', e => fetchFlights(parseInt(btn.dataset.page)));
            });
        })
        .catch(()=>{tableBody.innerHTML=`<tr><td colspan="${isAdmin ? 8 : 7}" class="empty">Could not load data</td></tr>`;});
}

if(isAdmin){
    document.addEventListener('click', function(e){
        if(e.target.classList.contains('delete-btn')){
            const id = e.target.dataset.id;
            if(confirm('Delete this flight?')){
                fetch('deleteFlight.php', {
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body: JSON.stringify({id})
                })
                .then(r=>r.json())
                .then(res=>{
                    if(res.success) fetchFlights(currentPage);
                    else alert(res.error || 'Could not delete flight');
                });
            }
        }
    });

    const addFlightForm = document.getElementById('addFlightForm');
    addFlightForm.addEventListener('submit', function(e){
        e.preventDefault();
        const formData = new FormData(this);
        fetch('addFlight.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                fetchFlights(currentPage);
                this.reset();
            } else {
                alert(data.error || 'Failed to add flight.');
            }
        })
        .catch(()=>alert('Failed to add flight.'));
    });
}

function escapeHtml(str){
    return String(str).replace(/[&<>"']/g,s=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[s]);
}

const debouncedFetch = debounce(fetchFlights,300);
airlineInput.addEventListener('input',debouncedFetch);
departureInput.addEventListener('input',debouncedFetch);
statusSelect.addEventListener('change',debouncedFetch);
clearBtn.addEventListener('click',()=>{airlineInput.value='';departureInput.value='';statusSelect.value='';fetchFlights();});

fetchFlights();
if(isAdmin){
    const importApiForm = document.getElementById('importApiForm');
    importApiForm.addEventListener('submit', function(e){
        e.preventDefault();
        const count = document.getElementById('apiCount').value;

        if(!count || count <= 0){
            alert('Enter a valid number of flights.');
            return;
        }

        fetch('importFlights.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ count: parseInt(count) })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                alert(`✅ Successfully imported ${data.imported} flights!`);
                fetchFlights();
            } else {
                alert(data.error || 'Failed to import flights.');
            }
        })
        .catch(()=>alert('Error connecting to API.'));
    });
}


</script>
</body>
</html>
