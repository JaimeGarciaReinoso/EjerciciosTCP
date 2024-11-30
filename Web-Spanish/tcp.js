

window.onload = (event) => {
    document.querySelectorAll("input").forEach((input) => {
        input.addEventListener("input", (event) => {

            //Check if null for opacity
            if(event.target.value == "NULL"){
                event.target.classList.add('nullinput');
            }else{
                event.target.classList.remove('nullinput');
            }

            //Check all row
            side = event.target.getAttribute("side");
            flag = false

            for (child of event.target.parentNode.parentNode.children){
                if(child.children.length > 0){
                    if((child.firstElementChild.value != "NULL") && (child.firstElementChild.getAttribute("side") == side)){
                        flag = true
                    }
                }
                else{
                    foundarrow = child
                }
            }            
            if(flag){
                if(side =="client")
                    foundarrow.classList.add("arrowright");
                else{
                    foundarrow.classList.add("arrowleft");
                }                
            }
            else{
                if(side =="client")
                    foundarrow.classList.remove("arrowright");
                else{
                    foundarrow.classList.remove("arrowleft");
                }
            }

        })
        var event = new Event('input');
        input.dispatchEvent(event)

    }) 


};