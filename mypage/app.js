const topbarButtons = document.querySelector("#buttons")

topbarButtons.addEventListener("click", e => {
    if (e.target.nodeName !== "BUTTON") {return; }
    
    // toggle active class
    for (button of topbarButtons.children) {
        if (button === e.target) {
            button.classList.add("active");
        } else {
            button.classList.remove("active");
        }
        
        // console.log(button);
    }
});