<?php
/**
 * include参照で利用されるコンポーネント
 */
?>
<style>
.hospital-group-label {
    font-weight: bold;
    min-width: 6em;
    margin-right: 0.2rem;
    cursor: pointer;
}
.hospital-group-label:before {
    border: 1px solid gray;
    font-weight: bold;
    margin-right: 0.1rem;
    width: 1em;
    height: 1em;
    line-height: 1em;
    padding: 0.2rem;
    display: -webkit-box;
    display: -ms-flexbox;
    display: -webkit-flex;
    display: flex;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
}
.hospital-group-label.all:before {
    content: "✓";
}
.hospital-group-label.partially:before {
    content: "■";
    color: #666;
}
.hospital-group-label.zero:before {
    content: "　";
}

.hospital-group-contents-label {
    margin-bottom: 0;
}

.hospital-group-label:hover,
.hospital-group-contents-label:hover {
    background-color: lightgoldenrodyellow;
}

.hospital-group-contents-label input ~ span {
    width: 7em;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hospital-group-contents-label input ~ span.deco {
    display: -webkit-box;
    display: -ms-flexbox;
    display: -webkit-flex;
    display: flex;
    -webkit-box-orient: horizontal;
    -webkit-box-direction: normal;
    -ms-flex-direction: row;
    flex-direction: row;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    width: 1em;
    height: 1em;
    min-width: 1em;
    min-height: 1em;
    margin-right: 0.2em;
    border: 1px solid rgb(103, 136, 152);
}

.hospital-group-contents-label input:checked ~ span.deco {
    background-color: rgb(32, 168, 216);
    color: white;
    border: none;
}

.hospital-group-contents-label input:active ~ span.deco,
.hospital-group-contents-label input:active:checked ~ span.deco {
    background-color: rgb(182, 228, 244);
    border: none;
}

.hospital-group-contents-label input:focus ~ span.deco {
    outline: 3px solid rgb(182, 228, 244);
}

.hospital-group-contents-label input:checked ~ span.deco:before {
    content: "✔";
    font-size: 10px;
}

#selected-hospital-container {
    gap: 0.2rem;
    min-height: 1.2em;
}
#selected-hospital-container > div {
    padding: 0.1rem 0.3rem;
    cursor: pointer;
    height: 1.2em;
    box-sizing: border-box;
}
#selected-hospital-container > div:hover {
    background-color: lightgoldenrodyellow;
}
#selected-hospital-container > div:before {
    content: "✓";
    border: 1px solid gray;
    font-weight: bold;
    margin-right: 0.1rem;
    width: 1em;
    height: 1em;
    line-height: 1em;
    padding: 0.2rem;
    display: -webkit-box;
    display: -ms-flexbox;
    display: -webkit-flex;
    display: flex;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
}
</style>
