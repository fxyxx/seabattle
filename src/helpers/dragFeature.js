import {parseLetterToNum} from "./parseLetterToNum.js";
import {shipsPort} from "../templates/templates.js";
import {addToLocalStorage} from "./addToLocalStorage.js";
import {getCellCoords} from "./getCellCoords.js";

const dragFeature = (rotateButton, autocompleteButton, clearButton) => {
        const ships = document.querySelectorAll(".ship");
        const cells = document.querySelectorAll(".cell");

        let coordinatesMatrix = Array.from({length: 10}, () => Array(10).fill(0));

        let currentShip = null;
        let prevShip = null;

        let currentCell = null;
        let curCoords = [];

        let dragPhantom = null;

        function dragStart(event) {
            dragPhantom = this.cloneNode(true);

            dragPhantom.style.position = "absolute";
            dragPhantom.style.opacity = "0.8";
            dragPhantom.classList.add("drag-phantom");

            document.body.appendChild(dragPhantom);

            updateDragPhantomPosition(event);
            event.dataTransfer.setDragImage(dragPhantom, 99999, 0);

            document.addEventListener("dragover", updateDragPhantomPosition);

            setTimeout(() => (this.style.display = "none"), 0);
            setCurrentShip(this);

            setBusyCells(currentShip.dataset.direction);

        }

        function updateDragPhantomPosition(event) {
            if (dragPhantom) {
                const shipLength = parseInt(currentShip?.dataset.length);
                const direction = currentShip?.dataset.direction;
                const phantom = dragPhantom;

                if (!isNaN(shipLength) && direction) {
                    let leftFactor, topFactor;

                    if (direction === "right") {
                        leftFactor = [2.5, 1.5, 1.25, 1.15][shipLength - 1];
                        topFactor = [3, 2.9, 2.8, 2.2][shipLength - 1];
                    } else if (direction === "down") {
                        leftFactor = [2, 2, 2, 2][shipLength - 1];
                        topFactor = [3, 1.5, 0.8, 0.65][shipLength - 1];
                    } else if (direction === "left") {
                        leftFactor = [2, 4, 6, 6][shipLength - 1];
                        topFactor = [2, 2, 2, 2][shipLength - 1];
                    } else if (direction === "up") {
                        leftFactor = [2.2, 2.2, 2.2, 2.2][shipLength - 1];
                        topFactor = [3, 100, -40, -52][shipLength - 1];
                    }

                    phantom.style.left = (event.clientX - phantom.offsetWidth / leftFactor) + "px";
                    phantom.style.top = (event.clientY - phantom.offsetHeight / topFactor) + "px";
                }
            }

        }

        function dragEnd(event) {
            document.removeEventListener("dragover", updateDragPhantomPosition);

            if (dragPhantom) {
                document.body.removeChild(dragPhantom);
                dragPhantom = null;
            }
            prevShip = this;
            setTimeout(() => (this.style.display = ""), 0);
            checkReady();

            const shipsCoordinates = getCellCoords(document.querySelectorAll(".cell"));
            addToLocalStorage(shipsCoordinates, "shipsCoordinates");
        }

        function dragOver(e) {
            e.preventDefault();

            setCurrentCell(this);
            highlightCells(currentCell, currentShip.dataset.direction, true);
        }

        function dragEnter(e) {

        }

        function dragLeave() {
            removeHighlight();
        }

        function dragDrop() {
            removeHighlight();


            if (currentShip?.dataset.position === "field") {
                clearBusyCells("clear");
            }

            if (currentShip !== null && checkCells(currentShip.dataset.direction)) {
                this.appendChild(currentShip);
                currentShip.dataset.position = "field";

                setBusyCells(currentShip.dataset.direction);
                setMatrix();
            } else {
                clearBusyCells("cancel");
            }
        }

        function highlightCells(cell, direction, isGreen) {
            const [startLetter, startNumber] = cell.dataset.coordinate.match(/([A-Z]+)(\d+)/).slice(1);
            const transformedLetter = parseLetterToNum(startLetter);

            const highlightColor = isGreen ? "#95F204F9" : "#D9001B7C";

            for (let i = -1; i <= parseInt(currentShip?.dataset.length); i++) {
                for (let j = -1; j <= 1; j++) {
                    let x, y;

                    if (direction === "left") {
                        x = transformedLetter - 1 + i;
                        y = parseInt(startNumber) - 1 + j;
                    } else if (direction === "up") {
                        x = transformedLetter - 1 + j;
                        y = parseInt(startNumber) - 1 + i;
                    } else if (direction === "right") {
                        x = transformedLetter - 1 - i;
                        y = parseInt(startNumber) - 1 + j;
                    } else if (direction === "down") {
                        x = transformedLetter - 1 + j;
                        y = parseInt(startNumber) - 1 - i;
                    }

                    if (x >= 0 && x < 10 && y >= 0 && y < 10) {
                        if (coordinatesMatrix[y][x] === 1 || isAdjacentToOtherShip(x, y)) {
                            highlightCell(x, y, "red");
                        } else {
                            highlightCell(x, y, highlightColor);
                        }
                    }
                }
            }
        }

        function isAdjacentToOtherShip(x, y) {
            for (let dx = -1; dx <= 1; dx++) {
                for (let dy = -1; dy <= 1; dy++) {
                    const newX = x + dx;
                    const newY = y + dy;
                    if (newX >= 0 && newX < 10 && newY >= 0 && newY < 10 && coordinatesMatrix[newY][newX] === 1) {
                        return true;
                    }
                }
            }
            return false;
        }


        function highlightCell(x, y, color) {
            const cellIndex = y * 10 + x;
            if (cellIndex >= 0 && cellIndex < cells.length) {
                const cell = cells[cellIndex];
                cell.style.backgroundColor = color;
            }
        }

        function removeHighlight() {
            cells.forEach(cell => {
                cell.style.backgroundColor = "";
            });
        }

        function setCurrentShip(ship) {
            currentShip?.children[0].classList.remove("current-ship");
            currentShip = ship;
            currentShip.children[0].classList.add("current-ship");
        }

        function setCurrentCell(cell) {
            return currentCell = cell;
        }

        function checkCells(shipOrientation) {
            const testCoords = [];
            const [startLetter, startNumber] = currentCell.dataset.coordinate.match(/([A-Z]+)(\d+)/).slice(1);
            const transformedLetter = parseLetterToNum(startLetter);

            if (shipOrientation === "right") {
                for (let i = 0; i < parseInt(currentShip.dataset.length); i++) {
                    testCoords.push({x: transformedLetter - 1 - i, y: parseInt(startNumber) - 1});
                }
            }
            if (shipOrientation === "down") {
                for (let i = 0; i < parseInt(currentShip.dataset.length); i++) {
                    testCoords.push({x: transformedLetter - 1, y: parseInt(startNumber) - 1 - i});
                }
            }
            if (shipOrientation === "left") {
                for (let i = 0; i < parseInt(currentShip.dataset.length); i++) {
                    testCoords.push({x: transformedLetter - 1 + i, y: parseInt(startNumber) - 1});
                }
            }
            if (shipOrientation === "up") {
                for (let i = 0; i < parseInt(currentShip.dataset.length); i++) {
                    testCoords.push({x: transformedLetter - 1, y: parseInt(startNumber) - 1 + i});
                }
            }

            const isValid = testCoords.some(coord => coord.x === -1
                || coord.y === -1
                || coord.x === 10
                || coord.y === 10);

            const checkAvailableCell = (matrix, shipCoords) => {
                const isSafe = (x, y) => {
                    for (let dx = -1; dx <= 1; dx++) {
                        for (let dy = -1; dy <= 1; dy++) {
                            const newX = x + dx;
                            const newY = y + dy;
                            if (newX >= 0 && newX < matrix[0].length && newY >= 0 && newY < matrix.length) {
                                if (matrix[newY][newX] === 1) {
                                    return false;
                                }
                            }
                        }
                    }
                    return true;
                };

                for (const coordinate of shipCoords) {
                    const x = coordinate.x;
                    const y = coordinate.y;

                    if (matrix[y][x] === 1 || !isSafe(x, y)) {
                        return false;
                    }
                }
                return true;
            };

            if (!isValid) {
                return checkAvailableCell(coordinatesMatrix, testCoords);
            }

            return !isValid;
        }

        function setBusyCells(shipOrientation) {
            const [startLetter, startNumber] = currentShip.parentNode.dataset.coordinate?.match(/([A-Z]+)(\d+)/).slice(1) ?? "Z1".split("");
            const transformedLetter = parseLetterToNum(startLetter);

            if (curCoords.length > 0) {
                curCoords = [];
            }

            if (shipOrientation === "right") {
                for (let i = 0; i < parseInt(currentShip.dataset.length); i++) {
                    curCoords.push({x: transformedLetter - 1 - i, y: parseInt(startNumber) - 1});
                }
            }
            if (shipOrientation === "down") {
                for (let i = 0; i < parseInt(currentShip.dataset.length); i++) {
                    curCoords.push({x: transformedLetter - 1, y: parseInt(startNumber) - 1 - i});
                }
            }
            if (shipOrientation === "left") {
                for (let i = 0; i < parseInt(currentShip.dataset.length); i++) {
                    curCoords.push({x: transformedLetter - 1 + i, y: parseInt(startNumber) - 1});
                }
            }
            if (shipOrientation === "up") {
                for (let i = 0; i < parseInt(currentShip.dataset.length); i++) {
                    curCoords.push({x: transformedLetter - 1, y: parseInt(startNumber) - 1 + i});
                }
            }

        }

        function clearBusyCells(action) {
            if (action === "clear") {

                curCoords.forEach(el => coordinatesMatrix[el.y][el.x] = 0);
            } else if (action === "cancel") {

                curCoords.forEach(el => coordinatesMatrix[el.y][el.x] = 1);
            }

        }

        function setMatrix() {
            coordinatesMatrix = Array.from({length: 10}, () => Array(10).fill(0));

            for (const cell of cells) {
                if (cell.hasChildNodes()) {
                    const [startLetter, startNumber] = cell.dataset.coordinate.match(/([A-Z]+)(\d+)/).slice(1);
                    const transformedLetter = parseLetterToNum(startLetter);

                    const direction = cell.children[0].dataset.direction;
                    const ship = cell.children[0].dataset.length;

                    if (direction === "right") {
                        for (let i = 0; i < parseInt(ship); i++) {
                            coordinatesMatrix[parseInt(startNumber) - 1][parseInt(transformedLetter) - 1 - i] = 1;
                        }
                    }
                    if (direction === "down") {
                        for (let i = 0; i < parseInt(ship); i++) {
                            coordinatesMatrix[parseInt(startNumber) - 1 - i][transformedLetter - 1] = 1;
                        }
                    }
                    if (direction === "left") {
                        for (let i = 0; i < parseInt(ship); i++) {
                            coordinatesMatrix[parseInt(startNumber) - 1][transformedLetter - 1 + i] = 1;
                        }
                    }
                    if (direction === "up") {
                        for (let i = 0; i < parseInt(ship); i++) {
                            coordinatesMatrix[parseInt(startNumber) - 1 + i][transformedLetter - 1] = 1;
                        }
                    }
                }
            }
        }

        function rotateShip() {
            const shipImage = currentShip?.children[0];
            clearBusyCells("clear");

            if (currentShip !== null && currentShip.dataset.direction === "right") {
                currentShip.dataset.direction = "down";

                if (checkCells(currentShip.dataset.direction)) {
                    currentShip.classList.add("deg90");

                    setBusyCells(currentShip.dataset.direction);
                    setMatrix();

                } else {
                    currentShip.dataset.direction = "right";
                    clearBusyCells("cancel");

                    shipImage.classList.add("rotate__error");
                    shipImage.addEventListener("animationend", () => {
                        shipImage.classList.remove("rotate__error");
                    });
                }

            } else if (currentShip !== null && currentShip.dataset.direction === "down") {
                currentShip.dataset.direction = "left";

                if (checkCells(currentShip.dataset.direction)) {
                    currentShip.classList.remove("deg90");
                    currentShip.classList.add("deg180");

                    setBusyCells(currentShip.dataset.direction);
                    setMatrix();

                } else {
                    currentShip.dataset.direction = "down";
                    clearBusyCells("cancel");

                    shipImage.classList.add("rotate__error");
                    shipImage.addEventListener("animationend", () => {
                        shipImage.classList.remove("rotate__error");
                    });
                }

            } else if (currentShip !== null && currentShip.dataset.direction === "left") {
                currentShip.dataset.direction = "up";

                if (checkCells(currentShip.dataset.direction)) {
                    currentShip.classList.remove("deg180");
                    currentShip.classList.add("deg270");

                    setBusyCells(currentShip.dataset.direction);
                    setMatrix();

                } else {
                    currentShip.dataset.direction = "left";
                    clearBusyCells("cancel");

                    shipImage.classList.add("rotate__error");
                    shipImage.addEventListener("animationend", () => {
                        shipImage.classList.remove("rotate__error");
                    });
                }

            } else if (currentShip !== null && currentShip.dataset.direction === "up") {
                currentShip.dataset.direction = "right";

                if (checkCells(currentShip.dataset.direction)) {
                    currentShip.classList.remove("deg270");

                    setBusyCells(currentShip.dataset.direction);
                    setMatrix();

                } else {
                    currentShip.dataset.direction = "up";
                    clearBusyCells("cancel");

                    shipImage.classList.add("rotate__error");
                    shipImage.addEventListener("animationend", () => {
                        shipImage.classList.remove("rotate__error");
                    });
                }

            }

            const shipsCoordinates = getCellCoords(document.querySelectorAll(".cell"));
            addToLocalStorage(shipsCoordinates, "shipsCoordinates");
        }

        function autocompleteShips() {
            const fieldSize = 10;
            const field = Array.from({length: fieldSize}, () => Array(fieldSize).fill(0));

            function canPlaceShip(row, col, length, direction) {
                if (direction === "left") {
                    if (col + length > fieldSize) return false;
                    for (let i = col - 1; i <= col + length; i++) {
                        for (let j = row - 1; j <= row + 1; j++) {
                            if (i >= 0 && i < fieldSize && j >= 0 && j < fieldSize && field[j][i] !== 0) return false;
                        }
                    }
                } else if (direction === "up") {
                    if (row + length > fieldSize) return false;
                    for (let i = col - 1; i <= col + 1; i++) {
                        for (let j = row - 1; j <= row + length; j++) {
                            if (i >= 0 && i < fieldSize && j >= 0 && j < fieldSize && field[j][i] !== 0) return false;
                        }
                    }
                } else if (direction === "right") {
                    if (col - length + 1 < 0) return false;
                    for (let i = col + 1; i >= col - length; i--) {
                        for (let j = row - 1; j <= row + 1; j++) {
                            if (i >= 0 && i < fieldSize && j >= 0 && j < fieldSize && field[j][i] !== 0) return false;
                        }
                    }
                } else if (direction === "down") {
                    if (row - length + 1 < 0) return false;
                    for (let i = col - 1; i <= col + 1; i++) {
                        for (let j = row + 1; j >= row - length; j--) {
                            if (i >= 0 && i < fieldSize && j >= 0 && j < fieldSize && field[j][i] !== 0) return false;
                        }
                    }
                }

                const shipsCoordinates = getCellCoords(document.querySelectorAll(".cell"));
                addToLocalStorage(shipsCoordinates, "shipsCoordinates");

                return true;
            }

            function placeShip(row, col, length, ship, direction) {
                const cells = document.querySelectorAll(".cell");

                const battleField = Array.from(cells).reduce((acc, item, index) => {
                    if (index % 10 === 0) {
                        acc.push([item]);
                    } else {
                        acc[acc.length - 1].push(item);
                    }
                    return acc;
                }, []);

                ship.classList.remove("deg90");
                ship.classList.remove("deg180");
                ship.classList.remove("deg270");

                ship.dataset.direction = direction;
                ship.dataset.position = "field";

                if (direction === "left") {
                    ship.classList.add("deg180");
                    battleField[row][col].appendChild(ship);
                } else if (direction === "up") {
                    ship.classList.add("deg270");
                    battleField[row][col].appendChild(ship);
                } else if (direction === "right") {
                    battleField[row][col].appendChild(ship);
                } else if (direction === "down") {
                    ship.classList.add("deg90");
                    battleField[row][col].appendChild(ship);
                }

                if (direction === "left") {
                    for (let i = col; i < col + length; i++) {
                        field[row][i] = 1;
                    }
                } else if (direction === "up") {
                    for (let i = row; i < row + length; i++) {
                        field[i][col] = 1;
                    }
                } else if (direction === "right") {
                    for (let i = col; i > col - length; i--) {
                        field[row][i] = 1;
                    }
                } else if (direction === "down") {
                    for (let i = row; i > row - length; i--) {
                        field[i][col] = 1;
                    }
                }

                setMatrix();
            }

            function generateShips() {
                const shipsDOM = document.querySelectorAll(".ship");

                const ships = [
                    {length: parseInt(shipsDOM[0].dataset.length), ship: shipsDOM[0]},
                    {length: parseInt(shipsDOM[1].dataset.length), ship: shipsDOM[1]},
                    {length: parseInt(shipsDOM[2].dataset.length), ship: shipsDOM[2]},
                    {length: parseInt(shipsDOM[3].dataset.length), ship: shipsDOM[3]},
                    {length: parseInt(shipsDOM[4].dataset.length), ship: shipsDOM[4]},
                    {length: parseInt(shipsDOM[5].dataset.length), ship: shipsDOM[5]},
                    {length: parseInt(shipsDOM[6].dataset.length), ship: shipsDOM[6]},
                    {length: parseInt(shipsDOM[7].dataset.length), ship: shipsDOM[7]},
                    {length: parseInt(shipsDOM[8].dataset.length), ship: shipsDOM[8]},
                    {length: parseInt(shipsDOM[9].dataset.length), ship: shipsDOM[9]},
                ];

                for (const ship of ships) {
                    let placed = false;
                    while (!placed) {
                        const row = Math.floor(Math.random() * fieldSize);
                        const col = Math.floor(Math.random() * fieldSize);
                        const direction = ["right", "down", "left", "up"][Math.floor(Math.random() * 4)];

                        if (canPlaceShip(row, col, parseInt(ship.length), direction)) {
                            placeShip(row, col, parseInt(ship.length), ship.ship, direction);
                            ship.y = row;
                            ship.x = col;
                            ship.direction = direction;
                            placed = true;
                        }
                    }
                }

                return ships;
            }

            currentShip?.children[0].classList.remove("current-ship");
            currentShip = null;
            generateShips();
            checkReady();
        }

        function clearField() {
            for (const cell of cells) {
                while (cell.firstChild) {
                    cell.removeChild(cell.firstChild);
                }
            }

            const shipContainer = document.querySelector(".ships-field");
            shipContainer.innerHTML = "";
            shipContainer.innerHTML = shipsPort;

            currentShip = null;
            dragFeature(rotateButton, autocompleteButton, clearButton);
            checkReady();

            const shipsCoordinates = getCellCoords(document.querySelectorAll(".cell"));
            addToLocalStorage(shipsCoordinates, "shipsCoordinates");
        }

        function checkReady() {
            const wrapper = document.querySelector(".ships-field");
            const portContainer = document.querySelectorAll(".s");
            const readyButton = document.getElementById("readyButton");

            portContainer.forEach(port => {
                const ship = port.querySelectorAll(".ship");

                if (ship.length === 0) {
                    port.remove();
                }
            });

            readyButton.disabled = wrapper.children.length !== 0;
        }

        ships.forEach(ship => {
            ship.addEventListener("dragstart", dragStart);
            ship.addEventListener("dragend", dragEnd);

            ship.addEventListener("click", function () {
                setCurrentShip(this);
                setCurrentCell(this.parentNode);
                setBusyCells(this.dataset.direction);
            });
        });

        cells.forEach(cell => {
            cell.addEventListener("dragover", dragOver);
            cell.addEventListener("dragenter", dragEnter);
            cell.addEventListener("dragleave", dragLeave);
            cell.addEventListener("drop", dragDrop);
        });

        rotateButton.addEventListener("click", rotateShip);
        clearButton.addEventListener("click", clearField);
        autocompleteButton.addEventListener("click", autocompleteShips);


    }
;

export {dragFeature};
