

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

    });

    //added by Wojciech Giermanek


    (function enableTableArrowKeyNavigation() {
        const tcpTable = document.querySelector("table");
        if (!tcpTable) return;

        const dataRows = Array.from(tcpTable.querySelectorAll("tr")).filter((row) =>
            row.querySelector('input[type="text"]')
        );

        const inputGridByRowAndCol = [];

        const isArrowKey = (key) =>
            key === "ArrowLeft" ||
            key === "ArrowRight" ||
            key === "ArrowUp" ||
            key === "ArrowDown";

        const canMoveFocusHorizontally = (inputEl, directionKey) => {
            const selectionStart = inputEl.selectionStart ?? 0;
            const selectionEnd = inputEl.selectionEnd ?? 0;
            const textLength = inputEl.value.length;

            if (directionKey === "ArrowLeft") return selectionStart === 0;
            if (directionKey === "ArrowRight") return selectionEnd === textLength;
            return true;
        };

        const focusAndSelect = (inputEl) => {
            inputEl.focus();
            inputEl.select();
        };

        dataRows.forEach((rowEl, rowIndex) => {
            const rowInputs = Array.from(rowEl.querySelectorAll('input[type="text"]'));

            inputGridByRowAndCol[rowIndex] = [];

            rowInputs.forEach((inputEl, colIndex) => {
                inputEl.dataset.gridRow = String(rowIndex);
                inputEl.dataset.gridCol = String(colIndex);

                inputGridByRowAndCol[rowIndex][colIndex] = inputEl;

                inputEl.addEventListener("keydown", (event) => {
                    if (!isArrowKey(event.key)) return;

                    if (
                        (event.key === "ArrowLeft" || event.key === "ArrowRight") &&
                        !canMoveFocusHorizontally(inputEl, event.key)
                    ) {
                        return;
                    }

                    event.preventDefault();

                    const currentRow = Number(inputEl.dataset.gridRow);
                    const currentCol = Number(inputEl.dataset.gridCol);

                    const targetRow =
                        event.key === "ArrowUp"
                            ? currentRow - 1
                            : event.key === "ArrowDown"
                                ? currentRow + 1
                                : currentRow;

                    const targetCol =
                        event.key === "ArrowLeft"
                            ? currentCol - 1
                            : event.key === "ArrowRight"
                                ? currentCol + 1
                                : currentCol;

                    const nextInput = inputGridByRowAndCol[targetRow]?.[targetCol] ?? null;

                    if (nextInput) focusAndSelect(nextInput);
                });
            });
        });
    })();



};
