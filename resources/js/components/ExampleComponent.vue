<template>
    <div class="example-component">
        <h1>Hello from Vue!</h1>
        <p>This is a sample component styled with plain CSS.</p>
        <button @click="count++">Count is: {{ count }}</button>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const count = ref(0);
</script>

<style scoped>
.example-component {
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #f9f9f9;
    text-align: center;
    max-width: 400px;
    margin: 20px auto;
}

h1 {
    color: #333;
}

button {
    padding: 10px 20px;
    background-color: #42b883;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

button:hover {
    background-color: #3aa876;
}
</style>
