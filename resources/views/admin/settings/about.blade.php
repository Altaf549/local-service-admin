@extends('admin.layouts.app')

@section('title', 'About Us')

@push('styles')
<style>
    .editor-container {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        min-height: 400px;
    }
    .editor-toolbar {
        background: #f8f9fa;
        border-bottom: 1px solid #ced4da;
        padding: 8px;
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }
    .editor-btn {
        padding: 5px 10px;
        border: 1px solid #ced4da;
        background: white;
        border-radius: 3px;
        cursor: pointer;
        font-size: 12px;
    }
    .editor-btn:hover {
        background: #e9ecef;
    }
    .editor-btn.active {
        background: #007bff;
        color: white;
    }
    #editor-content {
        padding: 15px;
        min-height: 350px;
        outline: none;
        overflow-y: auto;
        background: white;
        color: #212529;
        font-size: 14px;
        line-height: 1.5;
    }
    #editor-content:focus {
        background: #fff;
        box-shadow: inset 0 0 0 2px rgba(0,123,255,0.25);
    }
    #editor-content:empty:before {
        content: "Start typing here...";
        color: #6c757d;
        font-style: italic;
    }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">About Us</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.settings.update-about') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="about" class="form-label">Content</label>
                <div class="editor-container">
                    <div class="editor-toolbar">
                        <button type="button" class="editor-btn" onclick="formatText('bold')"><b>B</b></button>
                        <button type="button" class="editor-btn" onclick="formatText('italic')"><i>I</i></button>
                        <button type="button" class="editor-btn" onclick="formatText('underline')"><u>U</u></button>
                        <button type="button" class="editor-btn" onclick="formatText('strikeThrough')"><s>S</s></button>
                        <button type="button" class="editor-btn" onclick="formatText('insertUnorderedList')">• List</button>
                        <button type="button" class="editor-btn" onclick="formatText('insertOrderedList')">1. List</button>
                        <button type="button" class="editor-btn" onclick="formatText('justifyLeft')">←</button>
                        <button type="button" class="editor-btn" onclick="formatText('justifyCenter')">↔</button>
                        <button type="button" class="editor-btn" onclick="formatText('justifyRight')">→</button>
                        <button type="button" class="editor-btn" onclick="insertLink()">Link</button>
                        <button type="button" class="editor-btn" onclick="formatText('removeFormat')">Clear</button>
                    </div>
                    <div id="editor-content" contenteditable="true">{!! $about->value ?? '' !!}</div>
                </div>
                <input type="hidden" id="about" name="value">
            </div>
            <button type="submit" class="btn btn-primary">Update About Us</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editorContent = document.getElementById('editor-content');
        const hiddenInput = document.getElementById('about');
        
        // Update hidden input when editor content changes
        editorContent.addEventListener('input', function() {
            hiddenInput.value = editorContent.innerHTML;
        });
        
        // Set initial value in hidden input
        hiddenInput.value = editorContent.innerHTML;
    });
    
    function formatText(command) {
        document.execCommand(command, false, null);
        document.getElementById('editor-content').focus();
    }
    
    function insertLink() {
        const url = prompt('Enter URL:');
        if (url) {
            document.execCommand('createLink', false, url);
            document.getElementById('editor-content').focus();
        }
    }
</script>
@endpush

