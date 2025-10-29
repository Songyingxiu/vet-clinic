<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

// Get user's upcoming appointments
try {
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE user_id = ? AND status = 'scheduled' AND appointment_date >= ? ORDER BY appointment_date ASC, appointment_time ASC");
    $stmt->execute([$_SESSION['user_id'], date('Y-m-d')]);
    $upcoming_appointments = $stmt->fetchAll();
    
} catch (PDOException $e) {
    echo '<div class="col-12 text-center"><div class="alert alert-danger">Error loading appointments</div></div>';
    exit;
}

if (empty($upcoming_appointments)): ?>
    <div class="col-12 text-center">
        <div class="card card-bg">
            <div class="card-body py-5">
                <h5 class="text-600">No upcoming appointments</h5>
                <p class="text-500">Book an appointment to see it here!</p>
            </div>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($upcoming_appointments as $appointment): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card card-bg h-100 appointment-card">
                <div class="card-body">
                    <h5 class="fw-bold text-gradient"><?php echo htmlspecialchars($appointment['pet_name']); ?></h5>
                    <div class="mt-3">
                        <p class="mb-1"><strong>Date:</strong> <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></p>
                        <p class="mb-1"><strong>Time:</strong> <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></p>
                        <p class="mb-1"><strong>Service:</strong> <?php echo htmlspecialchars($appointment['service_type']); ?></p>
                        <p class="mb-1"><strong>Species:</strong> <?php echo htmlspecialchars($appointment['species']); ?></p>
                        <p class="mb-1"><strong>Breed:</strong> <?php echo htmlspecialchars($appointment['breed']); ?></p>
                        <?php if (!empty($appointment['notes'])): ?>
                            <p class="mb-1"><strong>Notes:</strong> <?php echo htmlspecialchars($appointment['notes']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="mt-3">
                        <a href="patient.php?edit_appointment=<?php echo $appointment['appointment_id']; ?>" class="btn btn-sm btn-outline-klean me-1">Edit</a>
                        <a href="patient.php?cancel_appointment=<?php echo $appointment['appointment_id']; ?>" 
                           class="btn btn-sm btn-outline-danger" 
                           onclick="return confirm('Are you sure you want to cancel this appointment?')">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>