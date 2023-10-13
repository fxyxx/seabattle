import {dragFeature} from "../helpers/dragFeature.js";
import {setCells} from "../helpers/setCells.js";
import {navigateTo} from "../routes/routing.js";
import {shipsPort} from "../templates/templates.js";
import {getCellCoords} from "../helpers/getCellCoords.js";
import {fetchApi} from "../api/fetchApi.js";
import {addToLocalStorage} from "../helpers/addToLocalStorage.js";
import {getFromLocalStorage} from "../helpers/getFromLocalStorage.js";
import {setShips} from "../helpers/setShips.js";

const template = (playerNickname, timer) => {
    return `
<div class="navbar">
    <img id="backButton" src=${"../public/images/backButton.svg"} alt="backButton">
    <div class="header__wrapper header__prepareComponent">
        <img src=${"../public/images/logo.svg"} alt="logo">
        <h1>Морський Бій</h1>
    </div>
    <img id="closeButton" src=${"../public/images/closeButton.svg"} alt="closeButton">
</div>  

<p>Розташуйте кораблі</p>

<div class="preparing-field">
    <div class="game-field">
        <p>${playerNickname}</p>
        <div class="battlefield__container">
             <div class="battlefield__numbers">
                <p>1</p><p>2</p><p>3</p><p>4</p><p>5</p><p>6</p><p>7</p><p>8</p><p>9</p><p>10</p>
            </div>
            <div class="battlefield__column">
                <div class="battlefield__letters">
                    <p>A</p><p>B</p><p>C</p><p>D</p><p>E</p><p>F</p><p>G</p><p>H</p><p>I</p><p>J</p>
                </div>
                <div class="battlefield__cells"></div>
                <div id="searchOverlay" class="">
                    <p class="search-overlay_text">Йде пошук суперника</p>
                </div>
            </div>
        </div>
        <button id="clearButton" class="clear-button">Очистити
            <img src=${"../public/images/clearBucket.svg"} alt="clear">
            <div class=""></div>
        </button>
    </div>
    <div class="ships-wrapper">
        <div class="ships-field">
            ${shipsPort}
        </div>
        <div class="buttons-wrapper">
            <button id="rotateButton" class="rotate-button action-button">
                <img src=${"../public/images/rotate.svg"} alt="rotate">
                <span>Повертання</span>
                <div class=""></div>
            </button>
            <button id="autocompleteButton" class="autocomplete-button action-button">
                <img src=${"../public/images/autocomplete.svg"} alt="autocomplete">
                <span>Автозаповнення</span>
                <div class=""></div>
            </button>
        </div>
    </div>
    <div class="search-wrapper">
        <button disabled id="readyButton" class="ready-button">Готовий</button>
        <div class="search-info hide__search">Очікуйте Гравця 2</div>
        <p class="stopwatch hide__search"><img src=${"../public/images/timer.svg"} alt="timer"><span>${timer}</span> сек</p>
    </div>
</div>

<div class="version version-prepare">V.1.0</div>
`;
};

let battleData = null;
const prepareComponent = (container, userNickname) => {
    const userNicknameLS = getFromLocalStorage("userNickname");

    let timer = 90;
    let stopWatch;
    let gameActive = false;

    const prepareComponent = document.createElement("div");
    prepareComponent.classList.add("prepareComponent");
    prepareComponent.innerHTML = template(userNickname === "" ? userNicknameLS : userNickname, timer);
    container.appendChild(prepareComponent);

    const battlefieldCells = document.querySelector(".battlefield__cells");
    const readyButton = document.getElementById("readyButton");
    const rotateButton = document.getElementById("rotateButton");
    const autocompleteButton = document.getElementById("autocompleteButton");
    const clearButton = document.getElementById("clearButton");

    setCells(battlefieldCells);
    dragFeature(rotateButton, autocompleteButton, clearButton);

    const userReady = () => {
        const hiddenElems = document.querySelectorAll(".search-wrapper > div, .search-wrapper > p");
        const seconds = document.querySelector(".stopwatch > span");

        const rotateDisable = document.querySelector(".rotate-button > div:last-child");
        const autocompleteDisable = document.querySelector(".autocomplete-button > div:last-child");
        const clearDisable = document.querySelector(".clear-button > div:last-child");
        const searchOverlay = document.getElementById("searchOverlay");

        hiddenElems.forEach(el => el.classList.remove("hide__search"));
        readyButton.textContent = "Відміна гри";
        seconds.textContent = `${timer}`;

        const setDisable = (bool) => {
            rotateButton.disabled = bool;
            autocompleteButton.disabled = bool;
            clearButton.disabled = bool;
        };
        const setClasses = (operation) => {
            if (operation === "add") {
                rotateDisable.classList.add("disabled-overlay");
                autocompleteDisable.classList.add("disabled-overlay");
                clearDisable.classList.add("disabled-overlay");
                searchOverlay.classList.add("search-overlay");
            } else if (operation === "remove") {
                rotateDisable.classList.remove("disabled-overlay");
                autocompleteDisable.classList.remove("disabled-overlay");
                clearDisable.classList.remove("disabled-overlay");
                searchOverlay.classList.remove("search-overlay");

                readyButton.textContent = "Готовий";
            }
        };

        if (gameActive) {
            clearInterval(stopWatch);
            timer = 90;

            setDisable(false);
            setClasses("remove");

            hiddenElems.forEach(el => el.classList.add("hide__search"));
            readyButton.textContent = "Готовий";

            if (battleData === null) {
                exitQueue(userNickname);
            }

            gameActive = false;
        } else {
            if (timer === 90) {
                const shipsCoordinates = getCellCoords(document.querySelectorAll(".cell"));
                battleData = null;

                startSearch(userNickname, shipsCoordinates);
                addToLocalStorage(shipsCoordinates, "shipsCoordinates");

                setDisable(true);
                setClasses("add");

                stopWatch = setInterval(() => {
                    if (timer > 0) {
                        timer--;
                        seconds.textContent = `${timer}`;

                    } else {
                        if (battleData?.isGameStart) {
                            clearInterval(stopWatch);
                            timer = 90;

                        } else {
                            clearInterval(stopWatch);
                            timer = 90;

                            exitQueue(userNickname);
                            setDisable(false);
                            setClasses("remove");
                            hiddenElems.forEach(el => el.classList.add("hide__search"));
                        }

                    }
                }, 1000);
            } else {
                clearInterval(stopWatch);
                timer = 90;

                exitQueue(userNickname);
                setDisable(false);
                setClasses("remove");
                hiddenElems.forEach(el => el.classList.add("hide__search"));
            }

            gameActive = true;
        }

        async function startSearch(username, shipsCoordinates) {
            try {
                const result = await fetchApi({
                    "login": username,
                    "shipsCoordinates": shipsCoordinates,
                    "timeStamp": new Date().getTime(),
                    "packageInitiator": "Prepare",
                    "packageType": "User start search the game",
                });

                if (result.isGameStart) {
                    battleData = result;
                    addToLocalStorage(result, "battleData");
                    navigateTo("/study/nakoskin/seabattle/acc/battle/");
                } else {

                }
            } catch (error) {
                console.error(error);
            }
        }

        async function exitQueue(username) {
            try {
                const result = await fetchApi({
                    "login": username,
                    "timeStamp": new Date().getTime(),
                    "packageInitiator": "Prepare",
                    "packageType": "Queue exit",
                });

            } catch (error) {
                console.error(error);
            }
        }
    };

    async function setQueue(username) {
        try {
            const result = await fetchApi({
                "login": username,
                "timeStamp": new Date().getTime(),
                "packageInitiator": "Prepare",
                "packageType": "Add user in queue",
            });

            if (result.isFound) {

            } else {

            }
        } catch (error) {
            console.error(error);
        }
    }

    setQueue(userNickname);


    async function setUserExit() {
        try {
            const result = await fetchApi({
                "login": userNickname,
                "timeStamp": new Date().getTime(),
                "packageInitiator": "Prepare",
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


    readyButton.addEventListener("click", userReady);
    document.getElementById("backButton").addEventListener("click", setUserExit);
    document.getElementById("closeButton").addEventListener("click", setUserExit);
};


export {prepareComponent, battleData};