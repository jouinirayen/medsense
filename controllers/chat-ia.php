<script>
function toggleChatbox() {
    const box = document.getElementById("chatbox");
    box.style.display = (box.style.display === "flex") ? "none" : "flex";
}

function sendMessage() {
    let message = document.getElementById("chatInput").value;
    if(message.trim() === "") return;

 
    addMessage("Vous", message);

    
    fetch("chat-ia.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ message })
    })
    .then(res => res.json())
    .then(data => {
        addMessage("IA", data.reply);
    });

    document.getElementById("chatInput").value = "";
}

function addMessage(sender, text) {
    const msgBox = document.getElementById("chatMessages");
    msgBox.innerHTML += `<p><strong>${sender}:</strong> ${text}</p>`;
    msgBox.scrollTop = msgBox.scrollHeight;
}
</script>
