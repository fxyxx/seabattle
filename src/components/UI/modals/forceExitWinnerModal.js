const template = () => `
<div class="forceExitModal__content modal">
    <div class="forceExitText__wrapper">
        <p>Ваш Суперник вийшов з гри.</p>
        <p>Ви автоматично перемагаєте!</p>
    </div>
</div>
`;

const forceExitWinnerModal = (container) => {
    const forceExitWinnerModal = document.createElement("div");
    forceExitWinnerModal.classList.add("forceExitModal");

    forceExitWinnerModal.innerHTML = template();
    container.appendChild(forceExitWinnerModal);

};

export {forceExitWinnerModal};