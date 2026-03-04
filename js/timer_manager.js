let globalTimerData = [];
const firedAutocycles = new Set();

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60)
    .toString()
    .padStart(2, "0");
  const secs = (seconds % 60).toString().padStart(2, "0");
  return `${mins}:${secs}`;
};

function updateAllDisplays() {
  document.querySelectorAll(".live-timer").forEach((el) => {
    const loadId = el.getAttribute("data-load-id");
    const data = globalTimerData.find((t) => t.load_id == loadId);
    if (!data) return;

    const isModal = el.id === "modalTimerDisplay";
    const isReady = data.remaining <= 0;

    // Update the UI
    el.innerText = isReady
      ? isModal
        ? "--:--"
        : "READY"
      : formatTime(data.remaining);
    el.classList.toggle("text-success", isReady);

    // Update Modal Buttons
    if (isModal) {
      const pauseBtn = document.getElementById("modalPauseBtn");
      const resetBtn = document.querySelector('button[onclick*="reset"]');

      if (pauseBtn) pauseBtn.style.display = isReady ? "none" : "inline-block";
      if (pauseBtn) pauseBtn.innerText = data.is_paused ? "Start" : "Pause";
      if (resetBtn) resetBtn.innerText = isReady ? "Back" : "Reset";
    }
  });
  // Update status display
  document.querySelectorAll(".status-badge").forEach((badge) => {
    const loadId = badge.getAttribute("data-load-id");
    const data = globalTimerData.find((t) => t.load_id == loadId);
    if (data) badge.innerText = data.status;
  });
}

function initTimer(loadId) {
  // Fetch state
  fetch(`backend/timer_action.php?action=get_state&load_id=${loadId}`)
    .then((res) => res.json())
    .then((data) => {
      const index = globalTimerData.findIndex((t) => t.load_id == loadId);
      if (index !== -1) {
        globalTimerData[index] = data;
      } else {
        globalTimerData.push(data);
      }
      updateAllDisplays();
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
