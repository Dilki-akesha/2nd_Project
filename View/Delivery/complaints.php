<?php
require_once __DIR__ . '/../../Model/Delivery/Issue.php';

$userId = $_SESSION['user_id'] ?? 5;
$issueModel = new Issue();
$issues = $issueModel->getAllByUser($userId);
?>

<div class="page <?= ($page === 'complaints' ? 'active' : '') ?>" id="page-complaints">
    <div class="section-header">
        <h3>Report Issue</h3>
        <span class="status-badge transit"><i class="fas fa-headset"></i> Support</span>
    </div>

    <div class="complaints-grid">

        <!-- Form -->
        <div class="complaint-form">
            <h4>Submit a New Issue</h4>

            <div class="form-group">
                <label for="issueCategory">Issue Category</label>
                <select id="issueCategory">
                    <option value="">Select Category</option>
                    <option>Farmer Unavailable</option>
                    <option>Buyer Unavailable</option>
                    <option>Pickup Issue</option>
                    <option>Incorrect Address</option>
                    <option>Incorrect Contact Details</option>
                    <option>Delivery Dispute</option>
                    <option>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="issueOrder">Related Order ID</label>
                <input type="text" id="issueOrder" placeholder="e.g. HLY-88291" />
            </div>

            <div class="form-group">
                <label for="issueDescription">Description</label>
                <textarea id="issueDescription" placeholder="Provide detailed information..."></textarea>
            </div>

            <div class="form-group">
                <label>Evidence Upload (Optional)</label>
                <div class="file-upload" onclick="document.getElementById('complaintFile').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Upload Evidence</span>
                    <input type="file" id="complaintFile" style="display:none;" />
                </div>
            </div>

            <button class="btn-primary" style="width:auto;padding:10px 32px;" id="submitComplaintBtn">
                <i class="fas fa-paper-plane"></i> Submit Issue
            </button>
        </div>

        <!-- Right Side -->
        <div>

            <!-- Commitment -->
            <div class="commitment-box">
                <h4><i class="fas fa-shield-alt"></i> Our Commitment</h4>
                <p>Reviewed within 24-48 hours.</p>
                <div class="commitment-tags">
                    <span><i class="fas fa-check-circle"></i> Transparent</span>
                    <span><i class="fas fa-check-circle"></i> Safety Protocols</span>
                    <span><i class="fas fa-check-circle"></i> Fair Resolution</span>
                </div>
            </div>

         <!-- Previous Issues (from DB) -->
         <h4 style="margin:20px 0 12px;">Previous Issues</h4>
         <div class="table-wrap">
              <div class="table-scroll">
                   <table>
                       <thead>
                            <tr>
                               <th>Issue ID</th>
                               <th>Order</th>
                               <th>Category</th>
                               <th>Date</th>
                               <th>Status</th>
                               <th>Admin Response</th>
                            </tr>
                       </thead>
                       <tbody>
                            <?php if (empty($issues)): ?>
                                <tr>
                                   <td colspan="6" style="text-align:center; padding:32px; color:var(--md-on-surface-variant);">
                                       <i class="fas fa-inbox" style="font-size: 24px; display:block; margin-bottom:8px;"></i>
                                           No issues submitted yet.
                                   </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($issues as $issue):
                                      $sMap = [
                                       'OPEN' => 'pending',
                                       'UNDER_REVIEW' => 'progress',
                                       'RESOLVED' => 'completed',
                                       'REJECTED' => 'pending'
                                      ];
                                      $sClass = $sMap[$issue['complaint_status']] ?? 'pending';
                                ?>
                                    <tr>
                                       <td><strong>#CAS-<?= str_pad($issue['complaint_id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                                       <td>#HLY-<?= htmlspecialchars($issue['order_id']) ?></td>
                                       <td><?= htmlspecialchars($issue['category']) ?></td>
                                       <td><?= htmlspecialchars($issue['created_date']) ?></td>
                                       <td><span class="status-badge <?= $sClass ?>"><?= str_replace('_', ' ', $issue['complaint_status']) ?></span></td>
                                       <td style="font-size:12px;color:var(--md-on-surface-variant);">
                                              <?= htmlspecialchars($issue['admin_response'] ?? '—') ?>
                                       </td>
                                    </tr>
                               <?php endforeach; ?>
                           <?php endif; ?>
                       </tbody>
                   </table>
               </div>
         </div>   

        </div>
    </div>
</div>