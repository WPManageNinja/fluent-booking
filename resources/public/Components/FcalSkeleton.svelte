<script>
    export let rows = 5;
</script>
  
<div class="fcal_skeleton">
    {#each Array(rows) as _}
       <div class="fcal_skeleton_item"></div>
    {/each}
</div>