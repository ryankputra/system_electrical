// Load notification duration
const notificationDuration = window.notificationDuration;

// Display notification
if (document.getElementById("notification")) {
	setTimeout(() => {
		document.getElementById("notification").classList.add("cust-fade-out");
	}, notificationDuration);
}