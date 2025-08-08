// notify.js

function showNotification(message, type = "info") {
  // Create notification container if not exists
  let container = document.getElementById("notification-container");
  if (!container) {
    container = document.createElement("div");
    container.id = "notification-container";
    container.style.position = "fixed";
    container.style.top = "20px";
    container.style.right = "20px";
    container.style.zIndex = "9999";
    container.style.maxWidth = "300px";
    document.body.appendChild(container);
  }

  // Create notification element
  const notification = document.createElement("div");
  notification.textContent = message;
  notification.style.backgroundColor = type === "success" ? "#4CAF50" :
                                   type === "error" ? "#f44336" : "#2196F3";
  notification.style.color = "white";
  notification.style.padding = "12px 20px";
  notification.style.marginTop = "10px";
  notification.style.borderRadius = "4px";
  notification.style.boxShadow = "0 2px 6px rgba(0,0,0,0.3)";
  notification.style.fontFamily = "Arial, sans-serif";
  notification.style.fontSize = "14px";
  notification.style.opacity = "0";
  notification.style.transition = "opacity 0.3s ease";

  container.appendChild(notification);

  // Fade in
  setTimeout(() => {
    notification.style.opacity = "1";
  }, 10);

  // Fade out and remove after 4 seconds
  setTimeout(() => {
    notification.style.opacity = "0";
    setTimeout(() => {
      notification.remove();
    }, 300);
  }, 4000);
}
