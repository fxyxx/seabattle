import {forceExitWinnerModal} from "./forceExitWinnerModal.js";
import {fetchApi} from "../../../api/fetchApi.js";

const template = (userLostText) => `
<div class="connectionLostModal__content modal">
    <div class="connectionLostText__wrapper">
        <p>${userLostText}</p>
        <p>Спроба повторного підключення...</p>
    </div>
    <div class="loader"></div>
    <div class="connectionLostStopwatch"><span>90</span> сек</div>
</div>
`;
let timerIsRunning = false;
let countdownTimeout = null;

const connectionLostModal = (container, lostType, action, opponentLastUpdateIntervalId, myLastUpdateIntervalId, myId, gameId) => {
    let timer = 90;

    const existingModal = document.querySelector(".connectionLostModal");

    if (action === "open" && !existingModal) {
        const connectionLostModal = document.createElement("div");
        connectionLostModal.classList.add("connectionLostModal");

        connectionLostModal.innerHTML = template(lostType);
        container.appendChild(connectionLostModal);

        if (!timerIsRunning) {
            timerIsRunning = true;
            const countdownElement = document.querySelector(".connectionLostStopwatch > span");

            const timerInterval = setInterval(() => {
                timer--;
                countdownElement.textContent = `${timer}`;
            }, 1000);

            countdownTimeout = setTimeout(() => {
                clearInterval(timerInterval);
                document.querySelector(".connectionLostModal")?.remove();
                forceExitWinnerModal(container);
                clearInterval(opponentLastUpdateIntervalId);
                clearInterval(myLastUpdateIntervalId);
                setTimeoutExitWinner();
            }, 90000);
        }
    } else if (action === "close" && existingModal) {
        existingModal.remove();
        timerIsRunning = false;
        clearTimeout(countdownTimeout);
    }

    async function setTimeoutExitWinner() {
        try {
            return await fetchApi({
                "myId": myId,
                "gameId": gameId,
                "timeStamp": new Date().getTime(),
                "packageInitiator": "Battle",
                "packageType": "Set timeout exit winner",
            });
        } catch (error) {
            console.error(error);
        }
    }
};


export {connectionLostModal, timerIsRunning};