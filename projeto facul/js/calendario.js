const calendarDays = document.getElementById("calendar-days");
const monthYear = document.getElementById("monthYear");
const eventList = document.getElementById("event-list");

let currentDate = new Date();
let events = JSON.parse(localStorage.getItem("events")) || {};
let selectedDate = null;

function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    monthYear.textContent = currentDate.toLocaleDateString("pt-BR", { month: "long", year: "numeric" });
    calendarDays.innerHTML = "";

    const dayNames = ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"];
    dayNames.forEach(day => {
        const div = document.createElement("div");
        div.className = "day-name";
        div.textContent = day;
        calendarDays.appendChild(div);
    });

    for (let i = 0; i < firstDay; i++) {
        const div = document.createElement("div");
        calendarDays.appendChild(div);
    }

    for (let day = 1; day <= daysInMonth; day++) {
        const div = document.createElement("div");
        div.className = "day";
        div.textContent = day;

        const dateKey = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

        // Tooltip
        if (events[dateKey]) {
            div.title = events[dateKey].join(", ");
            div.classList.add("has-event");
        }

        // Hoje
        const today = new Date();
        if (
            day === today.getDate() &&
            month === today.getMonth() &&
            year === today.getFullYear()
        ) {
            div.classList.add("today");
        }

        // Selecionado
        if (selectedDate === dateKey) {
            div.classList.add("selected");
        }

        div.onclick = () => {
            selectedDate = dateKey;
            showEvents(dateKey);
            renderCalendar(); // Re-renderiza para aplicar borda
        };

        calendarDays.appendChild(div);
    }
}


function changeMonth(offset) {
    currentDate.setMonth(currentDate.getMonth() + offset);
    renderCalendar();
}

function addEvent() {
    const title = document.getElementById("event-title").value.trim();
    if (!selectedDate || !title) {
        alert("Clique em um dia e preencha o título do evento.");
        return;
    }

    if (!events[selectedDate]) events[selectedDate] = [];
    events[selectedDate].push(title);
    localStorage.setItem("events", JSON.stringify(events));
    document.getElementById("event-title").value = "";
    renderCalendar();
    showEvents(selectedDate);
}

function showEvents(date) {
    const items = events[date] || [];
    eventList.innerHTML = items.length
        ? items.map(ev => `<li>${ev}</li>`).join('')
        : '<li>Sem eventos.</li>';
}

// Toggle do menu lateral
document.getElementById('menu-toggle').addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('hidden');
    document.querySelector('.container').classList.toggle('full-width');
});

// Logout
document.getElementById('logoutBtn').addEventListener('click', function () {
    alert('Você saiu da conta.');
    window.location.href = 'index.html';
});

renderCalendar();
