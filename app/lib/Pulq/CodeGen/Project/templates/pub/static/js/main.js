require.config({
    baseUrl: 'static',
    paths: {
        jquery: "js/libs/jquery",
        jsb: "js/libs/JsBehaviourToolkit"
    },
    shim: {
        jsb: {
            deps: ["jquery"],
            exports: "jsb"
        }
    }
});
