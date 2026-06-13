<div class="form-group">
    <label>Tiêu đề</label>
    <input type="text" name="title"
           value="{{ $news->title ?? '' }}"
           class="form-control">
</div>

<div class="form-group">
    <label>Nội dung</label>
    <textarea name="content"
              class="form-control"
              rows="6">{{ $news->content ?? '' }}</textarea>
</div>

<div class="form-group">
    <label>Ảnh</label>
    <input type="file" name="thumbnail" class="form-control">
</div>

<div class="form-group">
    <label>Trạng thái</label>
    <select name="status" class="form-control">
        <option value="draft">Nháp</option>
        <option value="published">Xuất bản</option>
        <option value="hidden">Ẩn</option>
    </select>
</div>

<div class="form-group">
    <label>Ngày đăng</label>
    <input type="datetime-local"
           name="published_at"
           class="form-control">
</div>