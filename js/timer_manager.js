// TIMER FUNCTIONS

let globalTimerData = [];

function updateAllDisplays() {
  // UI for timer
  document.querySelectorAll(".live-timer").forEach((el) => {
    const loadId = el.getAttribute("data-load-id");
    const data = globalTimerData.find((t) => t.load_id == loadId);
    const isModal = el.id === "modalTimerDisplay";

    if (!data) return;

    // UI elements inside the modal
    const pauseBtn = document.getElementById("modalPauseBtn");
    const resetBtn = document.querySelector(
      'button[onclick*="updateTimerDB"][onclick*="reset"]',
    );

    if (data.remaining <= 0) {
      el.innerText = isModal ? "--:--" : "READY";
      el.classList.add("text-success");

      if (isModal && pauseBtn) pauseBtn.style.display = "none";
      if (isModal && resetBtn) resetBtn.innerText = "Back";
    } else {
      let mins = Math.floor(data.remaining / 60);
      let secs = data.remaining % 60;
      el.innerText = `${mins.toString().padStart(2, "0")}:${secs.toString().padStart(2, "0")}`;
      el.classList.remove("text-success");

      if (isModal && pauseBtn) {
        pauseBtn.style.display = "inline-block";
        pauseBtn.innerText = data.is_paused ? "Start" : "Pause";
      }
      if (isModal && resetBtn) resetBtn.innerText = "Reset";
    }
  });

  // UI for statuses
  document.querySelectorAll(".status-badge").forEach((badge) => {
    const loadId = badge.getAttribute("data-load-id");
    const data = globalTimerData.find((t) => t.load_id == loadId);

    if (!data) return;

    badge.innerText = data.status;
  });
}

function syncWithServer() {
  fetch("backend/timers_sync.php")
    .then((res) => res.json())
    .then((data) => {
      globalTimerData = data;
      updateAllDisplays();
    })
    .catch((err) => console.error("Timer sync failed:", err));
}

const firedAutocycles = new Set();

// Local Tick
setInterval(() => {
  globalTimerData.forEach((timer) => {
    if (!timer.is_paused && timer.remaining > 0) {
      timer.remaining--;

      if (timer.remaining === 0) {
        const activeTimedStatuses = ["Washing", "Drying", "Folding"];

        if (activeTimedStatuses.includes(timer.status)) {
          autoCycleStatus(timer.load_id, "timer");
        }
      }
    }
  });
  updateAllDisplays();
}, 1000);

// Background Sync
setInterval(syncWithServer, 1000);
document.addEventListener("DOMContentLoaded", syncWithServer);
