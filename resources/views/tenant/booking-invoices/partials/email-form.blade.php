{{-- email content --}}
<form action="{{ route('tenant.booking-invoices.send-email', $bookingInvoice->id) }}" method="POST" class="form-horizontal">
  @csrf

  <div class="form-group mb-3">
    <label for="recipient_email" class="control-label ">Recipient</label>
    <input type="text" class="form-control" id="recipient_email" name="recipient_email" value="{{ old('recipient_email', $bookingInvoice->booking->guests->first()->email) }}" required>
  </div>
  <div class="form-group mb-3">
    <label for="mail_recipient_cc" class="control-label">CC</label>
    <input type="text" placeholder="Separate with commma(,) if multiple" id="mail_recipient_cc" name="mail_recipient_cc" class="form-control" value="{{ old('mail_recipient_cc') }}">
  </div>
  
  {{-- <div class="form-group">
    <label for="mail-subject" class="col-lg-2 control-label">Subject</label>
    <div class="col-lg-10">
      <input type="text" placeholder="" id="mail-subject" name="mail-subject" value="{{ $emailSubject }}" class="form-control">
    </div>
  </div> --}}
  {{-- <div class="form-group">
    <label for="mail-msg-text" class="col-lg-2 control-label">Message</label>
    <div class="col-lg-10">
      <textarea class="textarea_editor form-control" id="mail-msg-text" name="mail-msg-text" required rows="5" placeholder="Enter text ...">{{ $emailBody }}</textarea>
    </div>
  </div> --}}
  <div class="form-group mb-3">
    <div class="col-lg-offset-2 col-lg-10">
      <div class="checkbox checkbox-primary">
        <input id="send-copy" type="checkbox" name="send-copy" value="yes" >
        <label for="send-copy"> Send me a copy </label>
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-lg-offset-2 col-lg-10">
      <button type="submit" class="btn btn-success">Send</button>
    </div>
  </div>
</form>