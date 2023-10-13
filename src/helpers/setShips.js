import {getFromLocalStorage} from "./getFromLocalStorage.js";
import {fourCellShip, oneCellShip, threeCellShip, twoCellShip} from "../templates/templates.js";

function setShips(container) {
    const shipsCoordinates = getFromLocalStorage("shipsCoordinates");

    const cells = document.querySelectorAll(`${container} > .cell`);

    for (const cell of cells) {
        for (const data in shipsCoordinates) {
            if (cell.dataset.coordinate === data) {
                const key = shipsCoordinates[data];
                cell.innerHTML = checkLength(key);
                if (key.direction === "down") {
                    cell.children[0].classList.add("deg90");
                } else if (key.direction === "left") {
                    cell.children[0].classList.add("deg180");
                } else if (key.direction === "up") {
                    cell.children[0].classList.add("deg270");
                }
            }
        }
    }
}


function checkLength(key) {
    if (key.coordinates.length === 1) {
        return oneCellShip;
    } else if (key.coordinates.length === 2) {
        return twoCellShip;
    } else if (key.coordinates.length === 3) {
        return threeCellShip;
    } else if (key.coordinates.length === 4) {
        return fourCellShip;
    }
}

export {setShips};