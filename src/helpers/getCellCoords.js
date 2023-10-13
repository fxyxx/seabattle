const getCellCoords = (cells) => {
    const shipCoordinates = {};

    cells.forEach((cell) => {
        const coordinate = cell.dataset.coordinate;
        const ship = cell.querySelectorAll(".ship");

        if (ship.length > 0) {
            const direction = ship[0].dataset.direction;
            const length = parseInt(ship[0].dataset.length);
            const shipCoords = { coordinates: [coordinate], direction };

            for (let i = 1; i < length; i++) {
                if (direction === "down") {
                    shipCoords.coordinates.push(
                        String.fromCharCode(coordinate.charCodeAt(0)) +
                        (parseInt(coordinate.substr(1)) - i)
                    );
                } else if (direction === "up") {
                    shipCoords.coordinates.push(
                        String.fromCharCode(coordinate.charCodeAt(0)) +
                        (parseInt(coordinate.substr(1)) + i)
                    );
                } else if (direction === "right") {
                    shipCoords.coordinates.push(
                        String.fromCharCode(coordinate.charCodeAt(0) - i) +
                        coordinate.substr(1)
                    );
                } else if (direction === "left") {
                    shipCoords.coordinates.push(
                        String.fromCharCode(coordinate.charCodeAt(0) + i) +
                        coordinate.substr(1)
                    );
                }
            }

            shipCoordinates[coordinate] = shipCoords;
        }
    });

    return shipCoordinates;
};

export {getCellCoords}