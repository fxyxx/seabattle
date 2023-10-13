import {
    loginComponent,
    prepareComponent,
    battleComponent,
    battleResultComponent,
    notFoundComponent,
    forbiddenComponent,
} from "../components/components.js";
import {userNickname} from "../components/loginComponent.js";
import {battleData} from "../components/prepareComponent.js";
import {BattleResultTemplate} from "../components/battleComponent.js";
import {getFromLocalStorage} from "../helpers/getFromLocalStorage.js";

const appContainer = document.getElementById("app");

const navigateTo = (path) => {
    window.history.pushState({}, "", path);
    route(path);
};

const route = (path) => {
    appContainer.innerHTML = "";

    const userNicknameLS = getFromLocalStorage('userNickname')

    if (path === "/study/nakoskin/seabattle/") {
        loginComponent(appContainer);
    } else if (userNicknameLS === '') {
        forbiddenComponent(appContainer);
    } else {
        if (path === "/study/nakoskin/seabattle/acc/") {
            prepareComponent(appContainer, userNickname);
        } else if (path === "/study/nakoskin/seabattle/acc/battle/") {
            battleComponent(appContainer, userNickname, battleData);
        } else if (path === "/study/nakoskin/seabattle/acc/result-battle/") {
            battleResultComponent(appContainer, BattleResultTemplate, userNickname);
        }
    }


};


export {route, navigateTo};