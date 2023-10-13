const template = `
<h1>404</h1>
<p>Not Found</p>
`

const notFoundComponent = (container) => {
    const notFoundComponent = document.createElement('div');
    notFoundComponent.classList.add('notFoundComponent')

    notFoundComponent.innerHTML = template
    container.appendChild(notFoundComponent);
}

export {notFoundComponent}