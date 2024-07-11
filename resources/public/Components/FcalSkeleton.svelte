<script>
    export let items = 5;
</script>
  
<div class="fcal_skeleton">
    {#each Array(items) as _}
       <div class="fcal_skeleton_item"></div>
    {/each}
</div>