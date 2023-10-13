import {navigateTo} from "../routes/routing.js";
import {getFromLocalStorage} from "../helpers/getFromLocalStorage.js";
import {fetchApi} from "../api/fetchApi.js";
import {addToLocalStorage} from "../helpers/addToLocalStorage.js";

const template = (myNickname, opponentNickname) => `
<div class="navbar">
    <img id="backButton" src=${"../../public/images/backButton.svg"} alt="backButton">
    <div class="header__wrapper header__prepareComponent">
        <img src=${"../../public/images/logo.svg"} alt="logo">
        <h1>Морський Бій</h1>
    </div>
    <img id="closeButton" src=${"../../public/images/closeButton.svg"} alt="closeButton">
</div>
<div class="game-result_text">
    <p>${myNickname}, <span class=""></span></p>
</div>
<div class="game-result-nicknames_wrapper">
    <p>${myNickname}</p>
    <p>${opponentNickname}</p>
</div>
<div class="game-result_wrapper"></div>
<div class="game-result-buttons_wrapper">
    <button id="backToLoginBtn" class="game-result_btn">Вийти в головне меню</button>
    <button id="gameAgainBtn" class="game-result_btn">Грати ще</button>
</div>
<div class="version version-prepare">V.1.0</div>
`;


const battleResultComponent = (container, templateFields, userNickname) => {
    const iWinner = getFromLocalStorage("iWinner");
    const battleData = getFromLocalStorage("battleData");

    const battleResultComponent = document.createElement("div");
    battleResultComponent.classList.add("battleResultComponent");

    battleResultComponent.innerHTML = template(userNickname, battleData.opponentLogin);
    container.appendChild(battleResultComponent);

    const gameResults = document.querySelector(".game-result_wrapper");
    const setWinner = document.querySelector(".game-result_text span");
    gameResults.innerHTML = templateFields;

    if (iWinner) {
        setWinner.textContent = "Ви перемогли!";
        setWinner.classList.add('winner')
    } else {
        setWinner.textContent = "Ви програли!";
        setWinner.classList.add('loser')
    }

    async function setUserExit() {
        try {
            const result = await fetchApi({
                "myId": battleData.myId,
                "timeStamp": new Date().getTime(),
                "packageInitiator": "BattleResult",
                "packageType": "User exit",
            });
            if (result.isUserLeave) {
                addToLocalStorage("", "userNickname");
                navigateTo("/study/nakoskin/seabattle/");
            } else {

            }
        } catch (error) {
            console.error(error);
        }
    }

    async function setUserPlayAgain() {
        try {
            const result = await fetchApi({
                "myId": battleData.myId,
                "timeStamp": new Date().getTime(),
                "packageInitiator": "BattleResult",
                "packageType": "User play again",
            });
            if (result.isUserPlayAgain) {
                navigateTo("/study/nakoskin/seabattle/acc/");
            } else {

            }
        } catch (error) {
            console.error(error);
        }
    }

    document.getElementById("gameAgainBtn").addEventListener("click", setUserPlayAgain);
    document.getElementById("backToLoginBtn").addEventListener("click", setUserExit);
    document.getElementById("backButton").addEventListener("click", setUserPlayAgain);
    document.getElementById("closeButton").addEventListener("click", setUserExit);
};

export {battleResultComponent};