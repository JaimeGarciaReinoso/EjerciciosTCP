

window.onload = (event) => {
    document.querySelectorAll("input").forEach((input) => {
        input.addEventListener("input", (event) => {

            //Check if null for opacity
            if (event.target.value == "") {
                event.target.classList.add('nullinput');
            } else {
                event.target.classList.remove('nullinput');
            }

            //Check all row
            let hasClient = false;
            let hasServer = false;
            let foundarrow = null;

            for (child of event.target.parentNode.parentNode.children) {
                if (child.children.length > 0) {
                    let input = child.firstElementChild;
                    if (input.value != "" && !input.classList.contains("state-input")) {
                        if (input.getAttribute("side") == "client") {
                            hasClient = true;
                        } else if (input.getAttribute("side") == "server") {
                            hasServer = true;
                        }
                    }
                } else {
                    foundarrow = child;
                }
            }

            if (foundarrow) {
                foundarrow.classList.remove("arrowright", "arrowleft", "arrowboth");

                if (hasClient && hasServer) {
                    foundarrow.classList.add("arrowboth");
                } else if (hasClient) {
                    foundarrow.classList.add("arrowright");
                } else if (hasServer) {
                    foundarrow.classList.add("arrowleft");
                }
            }

        })
        var event = new Event('input');
        input.dispatchEvent(event)

    })


};
