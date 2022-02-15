    // Import the functions you need from the SDKs you need
    import { initializeApp } from "https://www.gstatic.com/firebasejs/9.6.6/firebase-app.js";
    import { getAnalytics } from "https://www.gstatic.com/firebasejs/9.6.6/firebase-analytics.js";
    // TODO: Add SDKs for Firebase products that you want to use
    // https://firebase.google.com/docs/web/setup#available-libraries

    // Your web app's Firebase configuration
    // For Firebase JS SDK v7.20.0 and later, measurementId is optional
    const firebaseConfig = {
    apiKey: "AIzaSyC6iqEObkg39vMKTla8CmnEblwn1HEu43I",
    authDomain: "polyamory-32638.firebaseapp.com",
    projectId: "polyamory-32638",
    storageBucket: "polyamory-32638.appspot.com",
    messagingSenderId: "865580994516",
    appId: "1:865580994516:web:6f7115666d65a90d639041",
    measurementId: "G-2YJTCHX87H"
};

    // Initialize Firebase
    const app = initializeApp(firebaseConfig);
    const analytics = getAnalytics(app);
