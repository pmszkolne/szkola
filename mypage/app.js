const topbarButtons = document.querySelector("#buttons")

topbarButtons.addEventListener("click", e => {
    if (e.target.nodeName !== "BUTTON") {return; }
    
    // toggle active class
    for (button of topbarButtons.children) {
        console.log(button);
    }
});