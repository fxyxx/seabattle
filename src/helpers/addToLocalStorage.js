const addToLocalStorage = (data, item) => {
    localStorage.removeItem(item);

    const newData = JSON.stringify(data);

    localStorage.setItem(item, newData);
};

export {addToLocalStorage};