<style>
    #credits {
        margin: 0;
        padding: 0;
    }

    .row {
        border-bottom: 1px dashed #555;
        list-style-type: none;
        margin-left: 0;
        margin-right: 0;
        display: block;
    }

    .name {
        background-color: #FFF;
        float: left;
        font-weight: bold;
        margin-bottom: -3px;
        padding-right: 5px;
        position: relative;
        top: 4px;
    }

    .position {
        background-color: #FFF;
        color: #555;
        float: right;
        padding-left: 5px;
        position: relative;
        right: -2px;
        text-align: right;
        top: 4px;
    }

    .category {
        border-bottom: 1px solid #555;
        color: #555;
        font-size: 110%;
        letter-spacing: 7px;
        list-style-type: none;
    }

    @media (max-width: 900px) {
        .row {
            border-bottom: none;
        }

        .name {
            float: none;
        }
    }

    @media (max-width: 576px) {
        .position {
            float: none;
            padding-left: 0;
            text-align: left;
        }
    }
</style>
