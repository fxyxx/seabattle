import {navigateTo, route} from "./routes/routing.js";
import {fetchApi} from "./api/fetchApi.js";
import {addToLocalStorage} from "./helpers/addToLocalStorage.js";
import {getFromLocalStorage} from "./helpers/getFromLocalStorage.js";

const screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
const screenHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;

if (screenWidth < 1024 || screenHeight < 768) {
    alert('Використання пристрою з непідтримуваною роздільною здатністю екрану. Рекомендована роздільна здатність - 1024х768');
}

async function setUserExit() {
    try {
        return await fetchApi({
            "login": getFromLocalStorage("userNickname"),
            "timeStamp": new Date().getTime(),
            "packageInitiator": "Prepare",
            "packageType": "User exit",
        });
    } catch (error) {
        console.error(error);
    }
}

window.addEventListener("beforeunload", () => {
    if (window.location.pathname !== '/study/nakoskin/seabattle/acc/battle/') {
        setUserExit();
    }
    addToLocalStorage("", "userNickname");
});

window.addEventListener("popstate", () => {
    route(window.location.pathname);
});

window.addEventListener("DOMContentLoaded", () => {
    route(window.location.pathname);
});

