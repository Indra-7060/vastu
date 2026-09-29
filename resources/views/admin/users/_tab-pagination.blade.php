<div class="pagination-wrap users-pagination">
    <div class="pagination-info">
        @if($paginator && $paginator->total())
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} entries
        @else
            Showing 0 to 0 of 0 entries
        @endif
    </div>
    @if($paginator)
        {{ $paginator->links() }}
    @endif
</div>
