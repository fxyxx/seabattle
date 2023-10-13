import {navigateTo} from "../../../routes/routing.js";
import {fetchApi} from "../../../api/fetchApi.js";

const template = () => `
<div class="forceExitModal__content modal">
    <div class="forceExitText__wrapper">
        <p>Ви впевнені, що хочете перервати гру?</p>
        <p>(Ви автоматично програєте)</p>
    </div>
    <div class="forceExitBtn__wrapper">
        <button id="forceExitYes">так</button>
        <button id="forceExitNo">ні</button>
    </div>
</div>
`;

const forceExitModal = (container, battleData, path, stopTimer, clearUpdateSearch) => {
    const forceExitModal = document.createElement("div");
    forceExitModal.classList.add("forceExitModal");

    forceExitModal.innerHTML = template();
    container.appendChild(forceExitModal);

    const userAction = async (action) => {
        if (action === "exitBattle") {
            if (path === "/study/nakoskin/seabattle/acc/") {
                try {
                    const result = await fetchApi({
                        "myId": battleData.myId,
                        "opponentId": battleData.opponentId,
                        "gameId": battleData.gameId,
                        "timeStamp": new Date().getTime(),
                        "packageInitiator": "Battle",
                        "packageType": "Force back",
                    });

                    if (result.isSetWinner) {
                        stopTimer();
                        clearUpdateSearch()
                        return navigateTo(path);
                    }

                } catch (error) {
                    console.error(error);
                }
            } else if (path === "/study/nakoskin/seabattle/") {
                try {
                    const result = await fetchApi({
                        "myId": battleData.myId,
                        "opponentId": battleData.opponentId,
                        "gameId": battleData.gameId,
                        "timeStamp": new Date().getTime(),
                        "packageInitiator": "Battle",
                        "packageType": "Force exit",
                    });

                    if (result.isSetWinner) {
                        stopTimer();
                        clearUpdateSearch()
                        return navigateTo(path);
                    }

                } catch (error) {
                    console.error(error);
                }
            }

        } else if (action === "exitModal") {
            return document.querySelector(".forceExitModal").remove();
        }
    };

    document.getElementById("forceExitYes").addEventListener("click", () => userAction("exitBattle"));
    document.getElementById("forceExitNo").addEventListener("click", () => userAction("exitModal"));
};

export {forceExitModal};