const setCells = (container) => {
    for (let number = 1; number <= 10; number++) {
        for (let letter = 'A'.charCodeAt(0); letter <= 'J'.charCodeAt(0); letter++) {
            const cell = document.createElement('div');
            cell.className = 'cell'
            cell.dataset.coordinate = `${String.fromCharCode(letter)}${number}`

            container.appendChild(cell);
        }
    }
}

export {setCells}