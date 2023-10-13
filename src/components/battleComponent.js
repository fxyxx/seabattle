import {setCells} from "../helpers/setCells.js";
import {navigateTo} from "../routes/routing.js";
import {fetchApi} from "../api/fetchApi.js";
import {fourCellShip, oneCellShip, threeCellShip, twoCellShip, miss, hit} from "../templates/templates.js";
import {forceExitModal} from "./UI/modals/forceExitModal.js";
import {connectionLostModal} from "./UI/modals/connectionLostModal.js";
import {forceExitWinnerModal} from "./UI/modals/forceExitWinnerModal.js";
import {getFromLocalStorage} from "../helpers/getFromLocalStorage.js";
import {addToLocalStorage} from "../helpers/addToLocalStorage.js";
import {parseLetterToNum} from "../helpers/parseLetterToNum.js";
import {setShips} from "../helpers/setShips.js";

let BattleResultTemplate = "";

const template = (playerNickname, opponentNickname, timerSeconds) => {
    return `
<div class="navbar">
    <img id="backButton" src=${"../../public/images/backButton.svg"} alt="backButton">
    <div class="header__wrapper header__prepareComponent">
        <img src=${"../../public/images/logo.svg"} alt="logo">
        <h1>Морський Бій</h1>
    </div>
    <img id="closeButton" src=${"../../public/images/closeButton.svg"} alt="closeButton">
</div>

<div class="timer">
    <p><img src=${"../../public/images/timer.svg"} alt="timer">час на хід</p>
    <p><span>${timerSeconds}</span> сек</p>
</div>

<div class="attempts">
    <div class="attempts__wrapper">
        <p>Залишилось пропусків ходу</p>
        <div id="my-attempts" class="attempts__quantity">${3}</div>
    </div>
    <div class="attempts__wrapper">
        <p>Залишилось пропусків ходу</p>
        <div id="opponent-attempts" class="attempts__quantity">${3}</div>
    </div>
</div>

<div class="battle-fields__wrapper">
    <div class="battle-fields">
        <div class="game-field">
            <p id="myTurn" class="">${playerNickname}</p>
            <div class="battlefield__container">
                 <div class="battlefield__numbers">
                    <p>1</p><p>2</p><p>3</p><p>4</p><p>5</p><p>6</p><p>7</p><p>8</p><p>9</p><p>10</p>
                </div>
                <div class="battlefield__column">
                    <div class="battlefield__letters">
                        <p>A</p><p>B</p><p>C</p><p>D</p><p>E</p><p>F</p><p>G</p><p>H</p><p>I</p><p>J</p>
                    </div>
                    <div class="battlefield-my__cells"></div>
                </div>
                <div class="disabled-overlay"></div>
            </div>
        </div>
        <div class="game-field">
            <p id="opponentTurn" class="">${opponentNickname}</p>
            <div class="battlefield__container">
                 <div class="battlefield__numbers">
                    <p>1</p><p>2</p><p>3</p><p>4</p><p>5</p><p>6</p><p>7</p><p>8</p><p>9</p><p>10</p>
                </div>
                <div class="battlefield__column">
                    <div class="battlefield__letters">
                        <p>A</p><p>B</p><p>C</p><p>D</p><p>E</p><p>F</p><p>G</p><p>H</p><p>I</p><p>J</p>
                    </div>
                    <div class="battlefield-opponent__cells"></div>
                    <div id="turnOverlay" class="">
                        <p class="turn-overlay_text">Хід суперника</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="version version-prepare">V.1.0</div>
`;
};

const battleComponent = (container, nickname, battleData) => {
    const battleDataLS = getFromLocalStorage("battleData");

    let timerIntervalTurn;
    let seconds = 30;
    let isTimerRunning = false;
    let listenerWinnerFromExit;
    let isYourTurn = battleData?.isYourTurn ?? battleDataLS.isYourTurn;
    let userNickname = nickname === "" ? getFromLocalStorage("userNickname") : nickname;

    let myLastUpdate = null;
    let opponentLastUpdate = null;
    let modalIsOpen = false;
    let isFirstIteration = true;

    const battleComponent = document.createElement("div");
    battleComponent.classList.add("battleComponent");
    battleComponent.innerHTML = template(userNickname,
        battleData?.opponentLogin ?? battleDataLS.opponentLogin,
        seconds);
    container.appendChild(battleComponent);
    const battleFieldsWrapper = document.querySelector(".battle-fields__wrapper");

    setCells(document.querySelector(".battlefield-my__cells"));
    setCells(document.querySelector(".battlefield-opponent__cells"));

    setShips(".battlefield-my__cells");
    startTimer();

    const isItReconnecting = getFromLocalStorage("isInReconnect");

    if (isItReconnecting) {
        const battleFieldsElement = document.querySelector(".battle-fields");

        battleFieldsElement.remove();
        battleFieldsWrapper.innerHTML += getFromLocalStorage("boardSnapshot");
    }

    const setMyLastUpdate = () => setInterval(setUserLastUpdate, 1000);
    const getOpponentLastUpdate = () => setInterval(getUserLastUpdate, 1000);

    const myLastUpdateIntervalId = setMyLastUpdate();
    const opponentLastUpdateIntervalId = getOpponentLastUpdate();

    function clearUpdateSearch() {
        clearInterval(myLastUpdateIntervalId);
        clearInterval(opponentLastUpdateIntervalId);
    }

    const checkAndUpdateModal = (battleComponent) => {
        if (myLastUpdate && opponentLastUpdate) {
            if (isFirstIteration) {
                isFirstIteration = false;
                return;
            }

            const timeDifference = Math.abs(myLastUpdate - opponentLastUpdate);
            if (timeDifference > 3 && !modalIsOpen) {
                modalIsOpen = true;
                stopTimer();
                connectionLostModal(battleComponent,
                    "У Гравця втрачено зв'язок з сервером.",
                    "open",
                    opponentLastUpdateIntervalId,
                    myLastUpdateIntervalId,
                    battleDataLS.myId,
                    battleDataLS.gameId);
            } else if (timeDifference <= 3 && modalIsOpen) {
                modalIsOpen = false;
                resetTimer();
                connectionLostModal(battleComponent,
                    "",
                    "close",
                    opponentLastUpdateIntervalId,
                    myLastUpdateIntervalId,
                    battleDataLS.myId,
                    battleDataLS.gameId);
            }
        }
    };

    async function setUserLastUpdate() {
        try {
            const result = await fetchApi({
                "myId": battleData?.myId ?? battleDataLS.myId,
                "opponentId": battleData?.opponentId ?? battleDataLS.opponentId,
                "timeStamp": new Date().getTime(),
                "packageInitiator": "Battle",
                "packageType": "Set user last update",
            });


            if (result.myLastUpdate) {
                myLastUpdate = new Date(result.myLastUpdate).getTime();
                checkAndUpdateModal(battleComponent);
            }


        } catch (error) {
            console.error(error);
        }
    }

    async function getUserLastUpdate() {
        try {
            const result = await fetchApi({
                "myId": battleData?.myId ?? battleDataLS.myId,
                "opponentId": battleData?.opponentId ?? battleDataLS.opponentId,
                "timeStamp": new Date().getTime(),
                "packageInitiator": "Battle",
                "packageType": "Get user last update",
            });

            if (result.opponentLastUpdate) {
                opponentLastUpdate = new Date(result.opponentLastUpdate).getTime();
                checkAndUpdateModal(battleComponent);
            }

        } catch (error) {
            console.error(error);
        }
    }

    const setTurn = (isYourTurn) => {
        const myTurn = document.getElementById("myTurn");
        const opponentTurn = document.getElementById("opponentTurn");
        const turnOverlay = document.getElementById("turnOverlay");

        if (isYourTurn) {
            opponentTurn.classList.remove("turn");
            myTurn.classList.add("turn");
            turnOverlay.classList.remove("turn-overlay");

        } else {
            myTurn.classList.remove("turn");
            opponentTurn.classList.add("turn");
            turnOverlay.classList.add("turn-overlay");
            listenerShotRequestMethod();
        }
    };

    setTurn(battleData?.isYourTurn ?? battleDataLS.isYourTurn);

    function startTimer() {
        if (!isTimerRunning) {
            timerIntervalTurn = setInterval(function () {
                seconds--;
                updateTimerDisplay();

                if (seconds === 0) {
                    stopTimer();
                    timerOverAction();
                }
            }, 1000);

            isTimerRunning = true;
        }
    }

    function stopTimer() {
        clearInterval(timerIntervalTurn);
        isTimerRunning = false;
    }

    function resetTimer() {
        stopTimer();
        seconds = 30;
        updateTimerDisplay();
        startTimer();
    }

    function updateTimerDisplay() {
        document.querySelector(".timer span").textContent = `${seconds}`;
    }

    function timerOverAction() {
        if (isYourTurn) setShotRequest("afk");
    }

    function changePlayerAttempts(myAttempts, opponentAttempts) {
        document.getElementById("my-attempts").textContent = `${3 - parseInt(myAttempts)}`;
        document.getElementById("opponent-attempts").textContent = `${3 - parseInt(opponentAttempts)}`;
    }

    async function setShotRequest(target, cell) {
        const battleFieldsElement = document.querySelector(".battle-fields");

        try {
            const result = await fetchApi({
                "myId": battleData?.myId ?? battleDataLS.myId,
                "opponentId": battleData?.opponentId ?? battleDataLS.opponentId,
                "gameId": battleData?.gameId ?? battleDataLS.gameId,
                "target": target,
                "timeStamp": new Date().getTime(),
                "packageInitiator": "Battle",
                "packageType": "Set Shot request",
            });

            if (result.isSuccess) {
                const intervalId = setInterval(async () => {
                    const listenerResponse = await listenerShotResponse(result);
                    if (listenerResponse.shotType !== null) {
                        clearInterval(intervalId);

                        if (listenerResponse.target !== "afk") {
                            setCell(cell,
                                parseInt(listenerResponse?.shotType),
                                listenerResponse?.startCoord,
                                listenerResponse?.shipLength);
                        }

                        if (listenerResponse.iWinner !== null) {
                            const clonedElement = battleFieldsElement.cloneNode(true);
                            const idsToRemove = ["myTurn", "opponentTurn", "turnOverlay"];

                            idsToRemove.forEach(id => {
                                const elementToRemove = clonedElement.querySelector("#" + id);
                                if (elementToRemove) {
                                    elementToRemove.parentNode.removeChild(elementToRemove);
                                }
                            });

                            BattleResultTemplate = clonedElement.outerHTML;

                            if (listenerResponse.iWinner) {
                                stopTimer();
                                clearUpdateSearch();
                                addToLocalStorage(listenerResponse.iWinner, "iWinner");
                                navigateTo("/study/nakoskin/seabattle/acc/result-battle/");
                            } else {
                                stopTimer();
                                clearUpdateSearch();
                                addToLocalStorage(listenerResponse.iWinner, "iWinner");
                                navigateTo("/study/nakoskin/seabattle/acc/result-battle/");
                            }
                        } else {
                            const clonedElement = battleFieldsElement.cloneNode(true);
                            addToLocalStorage(clonedElement.outerHTML, "boardSnapshot");

                            const gameData = getFromLocalStorage("battleData");
                            gameData.isYourTurn = listenerResponse.isYourTurn;
                            addToLocalStorage(gameData, "battleData");

                            isYourTurn = listenerResponse.isYourTurn;
                            resetTimer();
                            changePlayerAttempts(listenerResponse.myAfkCount ?? 0,
                                listenerResponse.opponentAfkCount ?? 0);
                            return setTurn(listenerResponse.isYourTurn);
                        }

                    }
                }, 200);

            } else {

            }
        } catch (error) {
            console.error(error);
        }
    }

    async function listenerShotResponse({myId, opponentId, gameId, target, turnNumber, request}) {
        try {
            return await fetchApi({
                "myId": myId,
                "opponentId": opponentId,
                "gameId": gameId,
                "target": target,
                "turnNumber": turnNumber,
                "request": request,
                "timeStamp": new Date().getTime(),
                "packageInitiator": "Battle",
                "packageType": "Listen Shot Response",
            });
        } catch (error) {
            console.error(error);
        }
    }

    async function setShotResponse({myId, opponentId, gameId, target, turnNumber}) {
        const battleFieldsElement = document.querySelector(".battle-fields");

        try {
            const result = await fetchApi({
                "myId": myId,
                "opponentId": opponentId,
                "gameId": gameId,
                "target": target,
                "turnNumber": turnNumber,
                "timeStamp": new Date().getTime(),
                "packageInitiator": "Battle",
                "packageType": "Set Shot response",
            });

            setOpponentShot(result?.target, parseInt(result?.shotType), result?.startCoord, result?.shipLength);

            if (result.iWinner !== null) {
                const clonedElement = battleFieldsElement.cloneNode(true);
                const idsToRemove = ["myTurn", "opponentTurn", "turnOverlay"];

                idsToRemove.forEach(id => {
                    const elementToRemove = clonedElement.querySelector("#" + id);
                    if (elementToRemove) {
                        elementToRemove.parentNode.removeChild(elementToRemove);
                    }
                });

                BattleResultTemplate = clonedElement.outerHTML;

                if (result.iWinner) {
                    stopTimer();
                    clearUpdateSearch();
                    addToLocalStorage(result.iWinner, "iWinner");
                    navigateTo("/study/nakoskin/seabattle/acc/result-battle/");
                } else {
                    stopTimer();
                    clearUpdateSearch();
                    addToLocalStorage(result.iWinner, "iWinner");
                    navigateTo("/study/nakoskin/seabattle/acc/result-battle/");
                }
            } else {
                const clonedElement = battleFieldsElement.cloneNode(true);
                addToLocalStorage(clonedElement.outerHTML, "boardSnapshot");

                const gameData = getFromLocalStorage("battleData");
                gameData.isYourTurn = result.isYourTurn;
                addToLocalStorage(gameData, "battleData");

                isYourTurn = result.isYourTurn;
                resetTimer();
                changePlayerAttempts(result.myAfkCount ?? 0, result.opponentAfkCount ?? 0);
                return setTurn(result.isYourTurn);
            }
        } catch (error) {
            console.error(error);
        }
    }

    async function listenerShotRequestMethod() {
        let isRequesting = false;

        const intervalId = setInterval(async () => {
            if (isRequesting) {
                return;
            }

            isRequesting = true;
            const listenerRequest = await listenerShotRequest(
                battleData?.myId ?? battleDataLS.myId,
                battleData?.opponentId ?? battleDataLS.opponentId,
                battleData?.gameId ?? battleDataLS.gameId);

            if (listenerRequest.target !== null) {
                clearInterval(intervalId);
                setShotResponse(listenerRequest);
            }

            isRequesting = false;
        }, 200);

        async function listenerShotRequest(myId, opponentId, gameId) {
            try {
                return await fetchApi({
                    "myId": myId,
                    "opponentId": opponentId,
                    "gameId": gameId,
                    "timeStamp": new Date().getTime(),
                    "packageInitiator": "Battle",
                    "packageType": "Listen Shot Request",
                });
            } catch (error) {
                console.error(error);
            }
        }
    }


    function setCell(cell, shotType, startCoord, shipLength) {
        const cells = document.querySelectorAll(".battlefield-opponent__cells > .cell");

        if (shotType === 0) {
            cell.innerHTML = miss;
        } else if (shotType === 1) {
            cell.innerHTML = hit;
        } else if (shotType >= 21 && shotType <= 24) {
            cell.innerHTML = hit;

            if (typeof startCoord !== "undefined") {
                for (const cellElement of cells) {
                    if (cellElement.dataset.coordinate.toLowerCase() === startCoord) {
                        if (parseInt(shipLength) === 1) {
                            cellElement.innerHTML += oneCellShip;
                        } else if (parseInt(shipLength) === 2) {
                            cellElement.innerHTML += twoCellShip;
                        } else if (parseInt(shipLength) === 3) {
                            cellElement.innerHTML += threeCellShip;
                        } else if (parseInt(shipLength) === 4) {
                            cellElement.innerHTML += fourCellShip;
                        }

                        if (shotType === 22) {
                            cellElement.classList.add("deg90");
                        } else if (shotType === 23) {
                            cellElement.classList.add("deg180");
                        } else if (shotType === 24) {
                            cellElement.classList.add("deg270");
                        }
                    }
                }
                setMissedCells(cells, shotType, startCoord, shipLength);
            }
        }
    }


    function setOpponentShot(target, shotType, startCoord, shipLength) {
        const cells = document.querySelectorAll(".battlefield-my__cells > .cell");

        for (const cell of cells) {
            if (cell.dataset.coordinate.toLowerCase() === target && shotType === 1) {
                cell.innerHTML += hit;
            } else if (cell.dataset.coordinate.toLowerCase() === target && shotType >= 21 && shotType <= 24) {
                cell.innerHTML += hit;

                if (!isItReconnecting) {
                    setMissedCells(cells, shotType, startCoord, shipLength);
                }

            } else if (cell.dataset.coordinate.toLowerCase() === target && shotType === 0) {
                cell.innerHTML += miss;
            }
        }
    }

    function setMissedCells(cells, shotType, startCoord, shipLength) {
        const [colChar, row] = startCoord.toUpperCase().match(/([A-J]+)(\d+)/).slice(1);
        const colIndex = parseLetterToNum(colChar);

        const shipCoordinates = [];

        if (shotType === 23) {
            for (let i = colIndex; i < colIndex + parseInt(shipLength); i++) {
                shipCoordinates.push(`${String.fromCharCode(64 + i)}${row}`);
            }
        } else if (shotType === 24) {
            for (let i = parseInt(row); i < parseInt(row) + parseInt(shipLength); i++) {
                shipCoordinates.push(`${colChar}${i}`);
            }
        } else if (shotType === 21) {
            for (let i = colIndex; i > colIndex - parseInt(shipLength); i--) {
                shipCoordinates.push(`${String.fromCharCode(64 + i)}${row}`);
            }
        } else if (shotType === 22) {
            for (let i = parseInt(row); i > parseInt(row) - parseInt(shipLength); i--) {
                shipCoordinates.push(`${colChar}${i}`);
            }
        }

        for (const coord of shipCoordinates) {
            const [col, row] = coord.toUpperCase().match(/([A-J]+)(\d+)/).slice(1);
            const colIndex = parseLetterToNum(col);

            for (let i = colIndex - 1; i <= colIndex + 1; i++) {
                for (let j = parseInt(row) - 1; j <= parseInt(row) + 1; j++) {
                    if (i >= 1 && i <= 10 && j >= 1 && j <= 10) {
                        const neighborCoord = `${String.fromCharCode(64 + i)}${j}`;
                        const neighborCell = Array.from(cells).find(c => c.dataset.coordinate === neighborCoord);
                        if (neighborCell && !neighborCell.querySelector(".hit")) {
                            neighborCell.innerHTML = miss;
                        }
                    }
                }
            }
        }
    }

    const setShot = () => {
        const cells = document.querySelectorAll(".battlefield-opponent__cells > .cell");

        cells.forEach(cell => cell.addEventListener("click", function () {
            if (!this.hasChildNodes()) {
                setShotRequest(this.dataset.coordinate.toLowerCase(), this);
            }
        }));
    };

    setShot();


    let connectionLost = false;
    const setConnectionLost = () => {
        clearInterval(lostListener);

        connectionLostModal(battleComponent);
    };

    const lostListener = setInterval(() => {
        if (connectionLost) {
            setConnectionLost();
        }
    }, 1000);


    async function listenWinnerFromExit() {
        try {
            const result = await fetchApi({
                "myId": battleDataLS.myId,
                "gameId": battleDataLS.gameId,
                "timeStamp": new Date().getTime(),
                "packageInitiator": "Battle",
                "packageType": "Force exit listener",
            });

            if (result.isSetWinner) {
                forceExitBtns(false);
                clearInterval(listenerWinnerFromExit);
                stopTimer();
                clearUpdateSearch();
                return forceExitWinnerModal(battleComponent);
            }
        } catch (error) {
            console.error(error);
        }
    }

    listenerWinnerFromExit = setInterval(listenWinnerFromExit, 2000);

    const forceExitBtns = (action) => {
        if (action) {
            document.getElementById("backButton").addEventListener("click", () =>
                forceExitModal(battleComponent, battleDataLS, "/study/nakoskin/seabattle/acc/", stopTimer, clearUpdateSearch));
            document.getElementById("closeButton").addEventListener("click", () =>
                forceExitModal(battleComponent, battleDataLS, "/study/nakoskin/seabattle/", stopTimer, clearUpdateSearch));
        } else {
            document.getElementById("backButton").addEventListener("click", async () => {
                try {
                    const result = await fetchApi({
                        "myId": battleData?.myId ?? battleDataLS.myId,
                        "timeStamp": new Date().getTime(),
                        "packageInitiator": "Battle",
                        "packageType": "Force back winner",
                    });

                    if (result.isStatusChanged) {
                        clearUpdateSearch();
                        stopTimer();
                        return navigateTo("/study/nakoskin/seabattle/acc/");
                    }

                } catch (error) {
                    console.error(error);
                }
            });
            document.getElementById("closeButton").addEventListener("click", async () => {
                try {
                    const result = await fetchApi({
                        "myId": battleData?.myId ?? battleDataLS.myId,
                        "timeStamp": new Date().getTime(),
                        "packageInitiator": "Battle",
                        "packageType": "Force exit winner",
                    });

                    if (result.isStatusChanged) {
                        clearUpdateSearch();
                        stopTimer();
                        return navigateTo("/study/nakoskin/seabattle/");
                    }

                } catch (error) {
                    console.error(error);
                }

            });
        }
    };

    forceExitBtns(true);

    async function setUserReconnect() {
        try {
            const result = await fetchApi({
                "myId": battleDataLS.myId,
                "timeStamp": new Date().getTime(),
                "packageInitiator": "Battle",
                "packageType": "Update user reconnect",
            });

        } catch (error) {
            console.error(error);
        }
    }

    window.addEventListener("beforeunload", () => {
        setUserReconnect();
        addToLocalStorage("", "userNickname");
    });
};


export {battleComponent, BattleResultTemplate};