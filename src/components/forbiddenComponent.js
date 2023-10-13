const template = `
<h1>403</h1>
<p>Forbidden</p>
`

const forbiddenComponent = (container) => {
    const redirectComponent = document.createElement('div');
    redirectComponent.classList.add('redirectComponent')

    redirectComponent.innerHTML = template
    container.appendChild(redirectComponent);
}

export {forbiddenComponent}