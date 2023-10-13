const getFromLocalStorage = (item) => {
    return JSON.parse(localStorage.getItem(item));
};

export {getFromLocalStorage};