<div class="border rounded-2 p-3" data-faq-row>
    <label class="form-label" for="faq-{{ $index }}-question">Pertanyaan</label>
    <input class="form-control mb-3" id="faq-{{ $index }}-question" name="faqs[{{ $index }}][question]" value="{{ $faq['question'] ?? '' }}" maxlength="200">
    <label class="form-label" for="faq-{{ $index }}-answer">Jawaban</label>
    <textarea class="form-control" id="faq-{{ $index }}-answer" name="faqs[{{ $index }}][answer]" maxlength="3000" rows="3">{{ $faq['answer'] ?? '' }}</textarea>
    <button class="btn btn-sm btn-outline-danger mt-3" type="button" data-faq-remove>Hapus FAQ</button>
</div>
