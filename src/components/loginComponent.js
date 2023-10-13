import {fetchApi} from "../api/fetchApi.js";
import {navigateTo} from "../routes/routing.js";
import {addToLocalStorage} from "../helpers/addToLocalStorage.js";
import {getFromLocalStorage} from "../helpers/getFromLocalStorage.js";

const template = () => {
    return `
<div class="header__wrapper header__loginComponent">
    <img src=${"./public/images/logo.svg"} alt="logo">
    <h1>Морський Бій</h1>
</div>
<form id="submitForm">
    <div class="input__wrapper">
        <input type="text" id="dataInput" placeholder="Введіть ваш нікнейм">
        <span class="status-icon">
            <img class="hidden" src=${"./public/images/typing.svg"} alt="status">
        </span>
    </div>
    <div id="validation-error">
        <div id="error__wrapper"></div>
        <div class="tooltip-text"></div>  
    </div>
    <button disabled type="submit" id="sendRequest">Розпочати гру</button>
</form>
<div class="version">V.1.0</div>
`;
};

let userNickname = "";
const loginComponent = (container) => {
    const loginComponent = document.createElement("div");
    loginComponent.classList.add("loginComponent");
    loginComponent.innerHTML = template();
    container.appendChild(loginComponent);

    const dataInput = document.getElementById("dataInput");
    const errorsWrapper = document.getElementById("error__wrapper");
    const validationError = document.getElementById("validation-error");
    const tooltipText = document.querySelector(".tooltip-text");
    const button = document.getElementById("sendRequest");
    const form = document.getElementById("submitForm");

    const statusImg = document.querySelector("#submitForm img");

    const validationErrors = {
        lengthError: "На жаль, помилка - дозволена довжина нікнейму від 3 до 10 символів.",
        patternError: "На жаль, помилка - нікнейм може містити літери (zZ-яЯ), цифри (0-9), спецсимволи (Word space, -, ', _).",
        startError: "Нікнейм повинен починатися з літери чи цифри.",
        endError: "Нікнейм повинен закінчуватися на літеру чи цифру.",
        uniquenessError: "На жаль, помилка - данний нікнейм вже зайнят, спробуйте іншій.",
    };

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const inputValue = dataInput.value;
        const validationErrors = validateNickname(inputValue);

        if (validationErrors.length === 0) {
            await checkUnique(inputValue);
        }
    });

    const validateNickname = (nickname) => {
        const minLength = 3;
        const maxLength = 10;

        const errors = [];
        if (nickname !== "") {
            if (nickname.length < minLength || nickname.length > maxLength) {
                errors.push(validationErrors.lengthError);
            }

            if (!/^[a-zA-Zа-яА-ЯіІєЄ0-9\s'\-_]+$/.test(nickname)) {
                errors.push(validationErrors.patternError);
            }

            if (!/^[a-zA-Zа-яА-ЯіІєЄ0-9]/.test(nickname)) {
                errors.push(validationErrors.startError);
            }

            if (!/[a-zA-Zа-яА-ЯіІєЄ0-9]$/.test(nickname)) {
                errors.push(validationErrors.endError);
            }
        }

        if (errors.length !== 0) {
            validationError.classList.add("show-tooltip");
        } else {
            validationError.classList.remove("show-tooltip");
        }


        if (errors.length === 0 && nickname !== "") {
            statusImg.src = "./public/images/correct.svg";
            button.classList.add("validated");
            button.disabled = false;
        } else {
            statusImg.src = "./public/images/incorrect.svg";
            button.classList.remove("validated");
            button.disabled = true;
        }

        updateErrorMessages(errors);

        return errors;
    };


    const updateErrorMessages = (errors) => {
        errorsWrapper.innerHTML = "";
        tooltipText.innerHTML = "";

        if (errors.length > 0) {
            errors.forEach(error => {
                const errorMessageElement = document.createElement("p");
                errorMessageElement.textContent = error;
                errorsWrapper.appendChild(errorMessageElement);

                const errorMessageElementTool = document.createElement("p");
                errorMessageElementTool.textContent = error;
                tooltipText.appendChild(errorMessageElementTool);
            });
        }
    };

    let validationTimeout;

    dataInput.addEventListener("input", () => {
        const inputValue = dataInput.value;

        if (inputValue === "") {
            statusImg.classList.add("hidden");
        } else {
            statusImg.classList.remove("hidden");
            statusImg.src = "./public/images/typing.svg";
        }

        clearTimeout(validationTimeout);

        validationTimeout = setTimeout(() => {
            validateNickname(inputValue);
        }, 200);

    });
    const checkUnique = async (nickname) => {
        try {
            const result = await fetchApi({
                "inputLogin": nickname,
                "timeStamp": new Date().getTime(),
                "packageInitiator": "Login",
                "packageType": "User authentication",
            });
            if (result.isAllowed) {
                if (result.isInReconnect) {
                    addToLocalStorage(result.isInReconnect, "isInReconnect");
                    addToLocalStorage(result.userLogin, "userNickname");
                    navigateTo("/study/nakoskin/seabattle/acc/battle/");
                } else {
                    addToLocalStorage(false, "isInReconnect");
                    userNickname = result.userLogin;
                    addToLocalStorage(result.userLogin, "userNickname");
                    navigateTo("/study/nakoskin/seabattle/acc/");
                }

            } else {
                statusImg.src = "./public/images/incorrect.svg";
                updateErrorMessages([validationErrors.uniquenessError]);
            }
        } catch (error) {
            console.error(error);
        }
    };

};

export {loginComponent, userNickname};